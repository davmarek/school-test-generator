<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'test_id',
        'group_id',
        'type',
        'text',
        'weight',
        'is_mandatory',
    ];

    protected $casts = [
        'type' => \App\Enums\QuestionType::class,
        'is_mandatory' => 'boolean',
        'weight' => 'integer',
    ];

    public function test(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(QuestionGroup::class);
    }

    public function options(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }
}
