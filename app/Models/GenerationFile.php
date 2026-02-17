<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GenerationFile Model
 *
 * Represents a single file within a template generation.
 * For HTML+CSS output, there's one file per page.
 * For framework output (React/Vue/Svelte/Angular), there are multiple
 * files including scaffold (package.json, config) and page components.
 */
class GenerationFile extends Model
{
    protected $fillable = [
        'generation_id',
        'page_generation_id',
        'file_path',
        'file_content',
        'file_type',
        'is_scaffold',
    ];

    protected $casts = [
        'is_scaffold' => 'boolean',
    ];

    /**
     * File type constants
     */
    public const TYPE_HTML = 'html';

    public const TYPE_TSX = 'tsx';

    public const TYPE_VUE = 'vue';

    public const TYPE_SVELTE = 'svelte';

    public const TYPE_TS = 'ts';

    public const TYPE_CSS = 'css';

    public const TYPE_JSON = 'json';

    public const TYPE_CONFIG = 'config';

    public const TYPE_JS = 'js';

    /**
     * Get the parent generation.
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * Get the page generation this file belongs to (if any).
     */
    public function pageGeneration(): BelongsTo
    {
        return $this->belongsTo(PageGeneration::class);
    }

    /**
     * Check if this is a scaffold/boilerplate file.
     */
    public function isScaffold(): bool
    {
        return $this->is_scaffold;
    }

    /**
     * Get the file extension from file_path.
     */
    public function getExtension(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Get the filename from file_path.
     */
    public function getFilename(): string
    {
        return pathinfo($this->file_path, PATHINFO_FILENAME);
    }

    /**
     * Get the directory from file_path.
     */
    public function getDirectory(): string
    {
        return pathinfo($this->file_path, PATHINFO_DIRNAME);
    }
}
