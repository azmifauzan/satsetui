<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Generation;
use App\Models\GenerationFailure;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    /**
     * Charge credits from user for generation
     */
    public function charge(User $user, int $amount, Generation $generation, string $description = null): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $generation, $description) {
            $balanceBefore = $user->credits;
            
            // Deduct credits
            $user->decrement('credits', $amount);
            $user->refresh();
            
            $balanceAfter = $user->credits;

            // Record transaction
            return CreditTransaction::create([
                'user_id' => $user->id,
                'generation_id' => $generation->id,
                'type' => CreditTransaction::TYPE_CHARGE,
                'amount' => -$amount, // Negative for deduction
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => Generation::class,
                'reference_id' => $generation->id,
                'description' => $description ?? "Credits charged for template generation",
                'metadata' => [
                    'generation_id' => $generation->id,
                    'template_name' => $generation->name,
                ],
            ]);
        });
    }

    /**
     * Refund credits to user on generation failure
     */
    public function refund(User $user, int $amount, Generation $generation, GenerationFailure $failure, string $reason = null): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $generation, $failure, $reason) {
            $balanceBefore = $user->credits;
            
            // Add credits back
            $user->increment('credits', $amount);
            $user->refresh();
            
            $balanceAfter = $user->credits;

            // Record transaction
            $transaction = CreditTransaction::create([
                'user_id' => $user->id,
                'generation_id' => $generation->id,
                'type' => CreditTransaction::TYPE_REFUND,
                'amount' => $amount, // Positive for refund
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_type' => GenerationFailure::class,
                'reference_id' => $failure->id,
                'description' => $reason ?? "Credits refunded due to generation failure",
                'metadata' => [
                    'failure_id' => $failure->id,
                    'failure_type' => $failure->failure_type,
                    'error_message' => $failure->error_message,
                    'generation_id' => $generation->id,
                ],
            ]);

            // Update failure record
            $failure->update([
                'credits_refunded' => $amount,
                'credits_refunded_at' => now(),
            ]);

            Log::info("Credits refunded", [
                'user_id' => $user->id,
                'amount' => $amount,
                'generation_id' => $generation->id,
                'failure_id' => $failure->id,
            ]);

            return $transaction;
        });
    }

    /**
     * Add credits to user (topup, bonus, etc)
     */
    public function addCredits(User $user, int $amount, string $type = CreditTransaction::TYPE_TOPUP, string $description = null, array $metadata = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $metadata) {
            $balanceBefore = $user->credits;
            
            // Add credits
            $user->increment('credits', $amount);
            $user->refresh();
            
            $balanceAfter = $user->credits;

            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?? "Credits added",
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Admin adjustment (can be positive or negative)
     */
    public function adminAdjustment(User $user, int $amount, User $adminUser, string $notes, array $metadata = []): CreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $adminUser, $notes, $metadata) {
            $balanceBefore = $user->credits;
            
            // Adjust credits (can be + or -)
            if ($amount > 0) {
                $user->increment('credits', abs($amount));
            } else {
                $user->decrement('credits', abs($amount));
            }
            
            $user->refresh();
            $balanceAfter = $user->credits;

            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => CreditTransaction::TYPE_ADJUSTMENT,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => "Admin adjustment",
                'admin_user_id' => $adminUser->id,
                'admin_notes' => $notes,
                'metadata' => $metadata,
            ]);
        });
    }

    /**
     * Get user's transaction history
     */
    public function getUserTransactions(User $user, int $limit = 50)
    {
        return CreditTransaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate statistics for admin dashboard
     */
    public function getStatistics(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'total_charges' => CreditTransaction::charges()
                ->where('created_at', '>=', $since)
                ->sum(DB::raw('ABS(amount)')),
            
            'total_refunds' => CreditTransaction::refunds()
                ->where('created_at', '>=', $since)
                ->sum('amount'),
            
            'total_topups' => CreditTransaction::topups()
                ->where('created_at', '>=', $since)
                ->sum('amount'),
            
            'net_revenue' => CreditTransaction::charges()
                ->where('created_at', '>=', $since)
                ->sum(DB::raw('ABS(amount)'))
                - CreditTransaction::refunds()
                    ->where('created_at', '>=', $since)
                    ->sum('amount'),
            
            'refund_rate' => $this->calculateRefundRate($days),
            
            'active_users' => CreditTransaction::where('created_at', '>=', $since)
                ->distinct('user_id')
                ->count('user_id'),
        ];
    }

    /**
     * Calculate refund rate (percentage of generations that failed)
     */
    private function calculateRefundRate(int $days): float
    {
        $since = now()->subDays($days);
        
        $totalCharges = CreditTransaction::charges()
            ->where('created_at', '>=', $since)
            ->count();
        
        $totalRefunds = CreditTransaction::refunds()
            ->where('created_at', '>=', $since)
            ->count();

        if ($totalCharges === 0) {
            return 0;
        }

        return round(($totalRefunds / $totalCharges) * 100, 2);
    }
}
