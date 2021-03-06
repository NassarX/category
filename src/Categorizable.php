<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Category Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Category Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

declare(strict_types=1);

namespace Rinvex\Category;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Categorizable
{
    /**
     * The Queued categories.
     *
     * @var array
     */
    protected $queuedCategories = [];

    /**
     * Register a created model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @param int             $priority
     *
     * @return void
     */
    abstract public static function created($callback, $priority = 0);

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     * @param int             $priority
     *
     * @return void
     */
    abstract public static function deleted($callback, $priority = 0);

    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param string      $related
     * @param string      $name
     * @param string|null $table
     * @param string|null $foreignKey
     * @param string|null $otherKey
     * @param bool        $inverse
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    abstract public function morphToMany($related, $name, $table = null, $foreignKey = null, $otherKey = null, $inverse = false);

    /**
     * Get category class name.
     *
     * @return string
     */
    public static function getCategoryClassName(): string
    {
        return Category::class;
    }

    /**
     * Get all attached categories to the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function categories(): MorphToMany
    {
        return $this->morphToMany(static::getCategoryClassName(), 'categorizable', 'categorizables', 'categorizable_id', 'category_id')->withTimestamps();
    }

    /**
     * Attach the given category(ies) to the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return void
     */
    public function setCategoriesAttribute($categories)
    {
        if (! $this->exists) {
            $this->queuedCategories = $categories;

            return;
        }

        $this->categorize($categories);
    }

    /**
     * Save attached category(ies) to the model.
     *
     * @return void
     */
    public static function bootCategorizable()
    {
        static::created(function (Model $categorizableModel) {
            if ($categorizableModel->queuedCategories) {
                $categorizableModel->categorize($categorizableModel->queuedCategories);

                $categorizableModel->queuedCategories = [];
            }
        });

        static::deleted(function (Model $categorizableModel) {
            $categorizableModel->recategorize(null);
        });
    }

    /**
     * Get the category list.
     *
     * @param string $keyColumn
     *
     * @return array
     */
    public function categoryList(string $keyColumn = 'slug'): array
    {
        return $this->categories()->pluck('name', $keyColumn)->toArray();
    }

    /**
     * Scope query with all the given categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $query
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     * @param string                                                  $column
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAllCategories(Builder $query, $categories, string $column = 'slug'): Builder
    {
        $categories = static::isCategoriesStringBased($categories)
            ? $categories : static::hydrateCategories($categories)->pluck($column);

        collect($categories)->each(function ($category) use ($query, $column) {
            $query->whereHas('categories', function (Builder $query) use ($category, $column) {
                return $query->where($column, $category);
            });
        });

        return $query;
    }

    /**
     * Scope query with any of the given categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $query
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     * @param string                                                  $column
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAnyCategories(Builder $query, $categories, string $column = 'slug'): Builder
    {
        $categories = static::isCategoriesStringBased($categories)
            ? $categories : static::hydrateCategories($categories)->pluck($column);

        return $query->whereHas('categories', function (Builder $query) use ($categories, $column) {
            $query->whereIn($column, (array) $categories);
        });
    }

    /**
     * Scope query with any of the given categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $query
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     * @param string                                                  $column
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCategories(Builder $query, $categories, string $column = 'slug'): Builder
    {
        return static::scopeWithAnyCategories($query, $categories, $column);
    }

    /**
     * Scope query without the given categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder                   $query
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     * @param string                                                  $column
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutCategories(Builder $query, $categories, string $column = 'slug'): Builder
    {
        $categories = static::isCategoriesStringBased($categories)
            ? $categories : static::hydrateCategories($categories)->pluck($column);

        return $query->whereDoesntHave('categories', function (Builder $query) use ($categories, $column) {
            $query->whereIn($column, (array) $categories);
        });
    }

    /**
     * Scope query without any categories.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAnyCategories(Builder $query): Builder
    {
        return $query->doesntHave('categories');
    }

    /**
     * Attach the given category(ies) to the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return $this
     */
    public function categorize($categories)
    {
        static::setCategories($categories, 'syncWithoutDetaching');

        return $this;
    }

    /**
     * Sync the given category(ies) to the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category|null $categories
     *
     * @return $this
     */
    public function recategorize($categories)
    {
        static::setCategories($categories, 'sync');

        return $this;
    }

    /**
     * Detach the given category(ies) from the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return $this
     */
    public function uncategorize($categories)
    {
        static::setCategories($categories, 'detach');

        return $this;
    }

    /**
     * Determine if the model has any the given categories.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return bool
     */
    public function hasCategory($categories): bool
    {
        // Single category slug
        if (is_string($categories)) {
            return $this->categories->contains('slug', $categories);
        }

        // Single category id
        if (is_int($categories)) {
            return $this->categories->contains('id', $categories);
        }

        // Single category model
        if ($categories instanceof Category) {
            return $this->categories->contains('slug', $categories->slug);
        }

        // Array of category slugs
        if (is_array($categories) && isset($categories[0]) && is_string($categories[0])) {
            return ! $this->categories->pluck('slug')->intersect($categories)->isEmpty();
        }

        // Array of category ids
        if (is_array($categories) && isset($categories[0]) && is_int($categories[0])) {
            return ! $this->categories->pluck('id')->intersect($categories)->isEmpty();
        }

        // Collection of category models
        if ($categories instanceof Collection) {
            return ! $categories->intersect($this->categories->pluck('slug'))->isEmpty();
        }

        return false;
    }

    /**
     * Determine if the model has any the given categories.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return bool
     */
    public function hasAnyCategory($categories): bool
    {
        return static::hasCategory($categories);
    }

    /**
     * Determine if the model has all of the given categories.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return bool
     */
    public function hasAllCategories($categories): bool
    {
        // Single category slug
        if (is_string($categories)) {
            return $this->categories->contains('slug', $categories);
        }

        // Single category id
        if (is_int($categories)) {
            return $this->categories->contains('id', $categories);
        }

        // Single category model
        if ($categories instanceof Category) {
            return $this->categories->contains('slug', $categories->slug);
        }

        // Array of category slugs
        if (is_array($categories) && isset($categories[0]) && is_string($categories[0])) {
            return $this->categories->pluck('slug')->count() == count($categories)
                   && $this->categories->pluck('slug')->diff($categories)->isEmpty();
        }

        // Array of category ids
        if (is_array($categories) && isset($categories[0]) && is_int($categories[0])) {
            return $this->categories->pluck('id')->count() == count($categories)
                   && $this->categories->pluck('id')->diff($categories)->isEmpty();
        }

        // Collection of category models
        if ($categories instanceof Collection) {
            return $this->categories->count() == $categories->count() && $this->categories->diff($categories)->isEmpty();
        }

        return false;
    }

    /**
     * Set the given category(ies) to the model.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     * @param string                                                  $action
     *
     * @return void
     */
    protected function setCategories($categories, string $action)
    {
        // Fix exceptional event name
        $event = $action == 'syncWithoutDetaching' ? 'attach' : $action;

        // Hydrate Categories
        $categories = static::hydrateCategories($categories)->pluck('id')->toArray();

        // Fire the category syncing event
        static::$dispatcher->fire("rinvex.category.{$event}ing", [$this, $categories]);

        // Set categories
        $this->categories()->$action($categories);

        // Fire the category synced event
        static::$dispatcher->fire("rinvex.category.{$event}ed", [$this, $categories]);
    }

    /**
     * Hydrate categories.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return \Illuminate\Support\Collection
     */
    protected function hydrateCategories($categories)
    {
        $isCategoriesStringBased = static::isCategoriesStringBased($categories);
        $isCategoriesIntBased = static::isCategoriesIntBased($categories);
        $field = $isCategoriesStringBased ? 'slug' : 'id';
        $className = static::getCategoryClassName();

        return $isCategoriesStringBased || $isCategoriesIntBased
            ? $className::query()->whereIn($field, (array) $categories)->get() : collect($categories);
    }

    /**
     * Determine if the given category(ies) are string based.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return bool
     */
    protected function isCategoriesStringBased($categories)
    {
        return is_string($categories) || (is_array($categories) && isset($categories[0]) && is_string($categories[0]));
    }

    /**
     * Determine if the given category(ies) are integer based.
     *
     * @param int|string|array|\ArrayAccess|\Rinvex\Category\Category $categories
     *
     * @return bool
     */
    protected function isCategoriesIntBased($categories)
    {
        return is_int($categories) || (is_array($categories) && isset($categories[0]) && is_int($categories[0]));
    }
}
