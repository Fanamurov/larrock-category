<?php

namespace Larrock\ComponentCategory\Models;

use Cache;
use Illuminate\Database\Eloquent\Model;
use Nicolaslopezj\Searchable\SearchableTrait;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMedia;
use Spatie\MediaLibrary\HasMedia\Interfaces\HasMediaConversions;
use Larrock\ComponentCategory\Facades\LarrockCategory;
use Larrock\ComponentCatalog\Facades\LarrockCatalog;
use Larrock\Core\Models\Seo;

/**
 * App\Models\Category
 *
 * @property integer $id
 * @property string $title
 * @property string $short
 * @property string $description
 * @property string $type
 * @property integer $parent
 * @property integer $level
 * @property string $url
 * @property integer $sitemap
 * @property integer $position
 * @property integer $active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category find($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereShort($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereType($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereParent($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereLevel($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereSitemap($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category wherePosition($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereActive($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category type($type)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category level($level)
 * @property string $forecast_url
 * @property string $map
 * @property integer $user_id
 * @property integer $to_rss
 * @property integer $sharing
 * @property integer $loads
 * @property-read mixed $full_url
 * @property-read mixed $class_element
 * @property-read mixed $user
 * @property-read mixed $short_wrap
 * @property-read mixed $first_image
 * @property-read mixed $map_coordinate
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\MediaLibrary\Media[] $media
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereForecastUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereMap($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereToRss($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereSharing($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereLoads($value)
 * @mixin \Eloquent
 * @property integer $attached
 * @property-read mixed $seotitle
 * @property-read mixed $parent_tree
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereAttached($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category search($search, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category searchRestricted($search, $restriction, $threshold = null, $entireText = false, $entireTextOnly = false)
 * @property string $component
 * @property integer $discount_id
 * @property-read mixed $get_seo_title
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereComponent($value)
 * @method static \Illuminate\Database\Query\Builder|\Larrock\ComponentCategory\Models\Category whereDiscountId($value)
 */
class Category extends Model implements HasMediaConversions
{
    use HasMediaTrait;

    public function registerMediaConversions()
    {
        $this->addMediaConversion('110x110')
            ->setManipulations(['w' => 110, 'h' => 110])
            ->performOnCollections('images');

        $this->addMediaConversion('140x140')
            ->setManipulations(['w' => 140, 'h' => 140])
            ->performOnCollections('images');

        $this->addMediaConversion('250x250')
            ->setManipulations(['w' => 250, 'h' => 250])
            ->performOnCollections('images');
    }

    protected $table = 'category';

	protected $fillable = ['title', 'short', 'description', 'component', 'parent', 'level', 'url', 'sitemap', 'rss', 'position', 'active'];

	protected $casts = [
		'position' => 'integer',
		'active' => 'integer',
		'sitemap' => 'integer',
		'rss' => 'integer',
		'level' => 'integer',
		'parent' => 'integer',
		'to_rss' => 'integer',
		'sharing' => 'integer',
	];

	protected $appends = [
		'full_url',
		'class_element',
		'first_image'
	];

	protected $guarded = ['user_id'];

	use SearchableTrait;

	// no need for this, but you can define default searchable columns:
	protected $searchable = [
		'columns' => [
			'category.title' => 10
		]
	];

	public function get_seo()
	{
		return $this->hasOne(Seo::class, 'id_connect', 'id')->whereTypeConnect('category');
	}

	public function getGetSeoTitleAttribute()
	{
		if($get_seo = Seo::whereIdConnect($this->id)->first()){
			return $get_seo->seo_title;
		}
		if($get_seo = Seo::whereUrlConnect($this->url)->first()){
			return $get_seo->seo_title;
		}
		return $this->title;
	}

    public function getGetParentSeoTitleAttribute()
    {
        if($get_seo = Seo::whereIdConnect($this->parent)->first()){
            return $get_seo->seo_title;
        }
        if($get_parent = LarrockCategory::getModel()->whereId($this->parent)->first()){
            if($get_seo = Seo::whereUrlConnect($get_parent->url)->first()){
                return $get_seo->seo_title;
            }else{
                return $get_parent->title;
            }
        }
        return $this->title;
    }

	public function get_child()
	{
		return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')->orderBy('position', 'DESC');
	}

	public function get_childActive()
	{
		return $this->hasMany(LarrockCategory::getModelName(), 'parent', 'id')->whereActive(1)->orderBy('position', 'DESC');
	}

	public function getParentTreeAttribute()
	{
		$list[] = $this;
		$key = 'tree_category'. $this->id;
		$list = Cache::remember($key, 1440, function() use ($list) {
			$get_data = $this->get_parent()->first();
			if(count($get_data) > 0){
				$list[] = $get_data;
				$get_data = $get_data->get_parent()->first();
				if(count($get_data) > 0){
					$list[] = $get_data;
					$get_data = $get_data->get_parent()->first();
					if(count($get_data) > 0){
						$list[] = $get_data;
					}
				}
			}
			return array_reverse($list);
		});
		return $list;
	}

	public function get_parent()
	{
		return $this->hasOne(LarrockCategory::getModelName(), 'id', 'parent');
	}

	public function getFullUrlAttribute()
	{
		$key = 'category-url'. $this->id;
		return Cache::remember($key, 1440, function() {
			if($search_parent = LarrockCategory::getModel()->whereId($this->parent)->first()){
				$prefix = '';
				if($search_parent->component === 'feed'){
					$prefix = '/feed';
				}
				if($search_parent->component === 'catalog'){
					$prefix = '/catalog';
				}
				if($search_parent_2 = LarrockCategory::getModel()->whereId($search_parent->parent)->first()){
					if($search_parent_3 = LarrockCategory::getModel()->whereId($search_parent_2->parent)->first()){
						if($this->get_category){
							return $prefix. '/'. $search_parent_3->url . '/' . $search_parent_2->url . '/' . $search_parent->url . '/' . $this->get_category->first()->url . '/' . $this->url;
						}
                        return $prefix. '/'. $search_parent_3->url . '/' . $search_parent_2->url . '/' . $search_parent->url . '/' . $this->url;
					}
                    if($this->get_category){
                        return $prefix. '/'. $search_parent_2->url . '/' . $search_parent->url . '/' . $this->get_category->first()->url . '/' . $this->url;
                    }
                    return $prefix. '/'. $search_parent_2->url . '/' . $search_parent->url . '/' . $this->url;
				}
                return $prefix. '/'. $search_parent->url . '/' . $this->url;
			}

            $prefix = '/';
            if($this->component === 'feed'){
                $prefix = '/feed/';
            }
            if($this->component === 'catalog'){
                $prefix = '/catalog/';
            }
            return $prefix . $this->url;
		});

	}

	public function getClassElementAttribute()
	{
		return 'category';
	}

	public function getUserAttribute()
	{
		return LarrockUsers::getModel()->whereId($this->user_id)->first();
	}

	public function get_tovars()
	{
		return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')->orderBy('position', 'DESC');
	}

	public function get_tovarsActive()
	{
		return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')->whereActive(1)->orderBy('position', 'DESC')->orderBy('cost', 'DESC');
	}

	public function get_tovarsAlias()
	{
		return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')->whereActive(1)->orderBy('position', 'DESC');
	}

	public function get_tovarsCount()
	{
		return $this->belongsToMany(LarrockCatalog::getModelName(), 'category_catalog', 'category_id', 'catalog_id')->count();
	}

	public function getShortWrapAttribute()
	{
		return mb_strimwidth($this->short, 0, 200, '...');
	}

	public function getImages()
	{
		return $this->hasMany('Spatie\MediaLibrary\Media', 'model_id', 'id')->where([['model_type', '=', LarrockCategory::getModelName()], ['collection_name', '=', 'images']])->orderBy('order_column', 'DESC');
	}

	public function getFirstImage()
	{
		return $this->hasOne('Spatie\MediaLibrary\Media', 'model_id', 'id')->where([['model_type', '=', LarrockCategory::getModelName()], ['collection_name', '=', 'images']])->orderBy('order_column', 'DESC');
	}

	public function getFirstImageAttribute()
	{
		$value = Cache::remember('image_f_category'. $this->id, 1440, function() {
			if($get_image = $this->getMedia('images')->sortByDesc('order_column')->first()){
				return $get_image->getUrl();
			}
            return '/_assets/_front/_images/empty_big.png';
		});
		return $value;
	}

    public function getFiles()
    {
        return $this->hasMany('Spatie\MediaLibrary\Media', 'model_id', 'id')->where([['model_type', '=', LarrockCategory::getModelName()], ['collection_name', '=', 'files']])->orderBy('order_column', 'DESC');
    }

	public function get_feed()
	{
		return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->orderBy('position', 'DESC');
	}

	public function get_feedActive()
	{
		return $this->hasMany(LarrockFeed::getModelName(), 'category', 'id')->whereActive(1)->orderBy('position', 'DESC');
	}

	public function get_soputka()
	{
		return $this->belongsToMany(LarrockCategory::getModelName(), 'category_link', 'category_id', 'category_id_link')->orderBy('position', 'DESC');
	}

	public function get_soputkaTovars()
	{
		$find_categories = $this->belongsToMany(LarrockCategory::getModelName(), 'category_link', 'category_id', 'category_id_link')->orderBy('position', 'DESC')->get(['id']);
		$list_categories = [];
		foreach($find_categories as $category){
			$list_categories[] = $category->id;
		}
		return Catalog::whereActive(1)->whereHas('category', function ($q) use ($list_categories){
			$q->whereIn('category.id', $list_categories);
		})->get();
	}

	public function get_discount()
    {
        return $this->hasOne(LarrockDiscount::getModelName(), 'id', 'discount_id');
    }
}
