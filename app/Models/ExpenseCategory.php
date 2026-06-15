<?php

namespace App\Models;

use App\Enums\ExpenseCategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'title'];

    protected function casts(): array
    {
        return [
            'type'       => ExpenseCategoryType::class,
            'deleted_at' => 'datetime',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->title ?? ucwords(str_replace('_', ' ', $this->type->value));
    }
}
