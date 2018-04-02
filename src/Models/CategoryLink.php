<?php

namespace Larrock\ComponentCategory\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \Larrock\ComponentCategory\Models\CategoryLink.
 *
 * @property int $category_id
 * @property int $catalog_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereCatalogId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $id
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereId($value)
 * @property int $category_id_link
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\CategoryLink whereCategoryIdLink($value)
 */
class CategoryLink extends Model
{
    protected $table = 'category_link';

    protected $fillable = ['category_id', 'category_id_link'];
}
