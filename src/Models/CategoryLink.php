<?php

namespace Larrock\ComponentCategory\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CategoryCatalog
 *
 * @property integer $category_id
 * @property integer $catalog_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryCatalog whereCategoryId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryCatalog whereCatalogId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryCatalog whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryCatalog whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property integer $id
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryCatalog whereId($value)
 * @property integer $category_id_link
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CategoryLink whereCategoryIdLink($value)
 */
class CategoryLink extends Model
{
    protected $table = 'category_link';

	protected $fillable = ['category_id', 'category_id_link'];
}
