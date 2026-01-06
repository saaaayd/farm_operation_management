<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{

    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
        'planting_id',
        'user_id',
        'payment_method',
        'receipt_number',
        'notes',
        'related_entity_type',
        'related_entity_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
        'related_entity_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Category constants
     */
    const CATEGORY_SEEDS = 'seeds';
    const CATEGORY_FERTILIZER = 'fertilizer';
    const CATEGORY_PESTICIDE = 'pesticide';
    const CATEGORY_LABOR = 'labor';
    const CATEGORY_EQUIPMENT = 'equipment';
    const CATEGORY_UTILITIES = 'utilities';
    const CATEGORY_MAINTENANCE = 'maintenance';
    const CATEGORY_INVENTORY_PURCHASE = 'inventory_purchase';
    const CATEGORY_OTHER = 'other';

    /**
     * Related entity type constants
     */
    const ENTITY_TYPE_FIELD = 'field';
    const ENTITY_TYPE_PLANTING = 'planting';
    const ENTITY_TYPE_HARVEST = 'harvest';
    const ENTITY_TYPE_TASK = 'task';
    const ENTITY_TYPE_LABORER = 'laborer';
    const ENTITY_TYPE_LABOR_WAGE = 'labor_wage';
    const ENTITY_TYPE_INVENTORY_ITEM = 'inventory_item';

    /**
     * Get the planting associated with this expense
     */
    public function planting()
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * The farmer that owns the expense entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related task if linked
     */
    public function task()
    {
        if ($this->related_entity_type === self::ENTITY_TYPE_TASK) {
            return Task::find($this->related_entity_id);
        }
        return null;
    }

    /**
     * Get the related laborer if linked
     */
    public function laborer()
    {
        if ($this->related_entity_type === self::ENTITY_TYPE_LABORER) {
            return Laborer::find($this->related_entity_id);
        }
        return null;
    }

    /**
     * Get the related labor wage if linked
     */
    public function laborWage()
    {
        if ($this->related_entity_type === self::ENTITY_TYPE_LABOR_WAGE) {
            return LaborWage::find($this->related_entity_id);
        }
        return null;
    }

    /**
     * Get the related inventory item if linked
     */
    public function inventoryItem()
    {
        if ($this->related_entity_type === self::ENTITY_TYPE_INVENTORY_ITEM) {
            return InventoryItem::find($this->related_entity_id);
        }
        return null;
    }

    /**
     * Get the farmer (through planting -> field -> user)
     */
    public function getFarmerAttribute()
    {
        return $this->planting?->field?->user;
    }

    /**
     * Get the crop type (through planting)
     */
    public function getCropTypeAttribute()
    {
        return $this->planting?->crop_type;
    }

    /**
     * Scope for monthly expenses
     */
    public function scopeMonthly($query, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $query->whereMonth('date', $month)
            ->whereYear('date', $year);
    }

    /**
     * Scope for expenses by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get all valid categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SEEDS,
            self::CATEGORY_FERTILIZER,
            self::CATEGORY_PESTICIDE,
            self::CATEGORY_LABOR,
            self::CATEGORY_EQUIPMENT,
            self::CATEGORY_UTILITIES,
            self::CATEGORY_MAINTENANCE,
            self::CATEGORY_INVENTORY_PURCHASE,
            self::CATEGORY_OTHER,
        ];
    }

    /**
     * Get all valid entity types
     */
    public static function getEntityTypes(): array
    {
        return [
            self::ENTITY_TYPE_FIELD,
            self::ENTITY_TYPE_PLANTING,
            self::ENTITY_TYPE_HARVEST,
            self::ENTITY_TYPE_TASK,
            self::ENTITY_TYPE_LABORER,
            self::ENTITY_TYPE_LABOR_WAGE,
            self::ENTITY_TYPE_INVENTORY_ITEM,
        ];
    }
}