<?php

declare(strict_types=1);

namespace CzechitasApp\Services\Models;

use CzechitasApp\Models\Category;
use CzechitasApp\Models\Term;
use CzechitasApp\Services\UploadStorageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Resampler\Resampler;

/**
 * @method Category getContext()
 * @method Builder<Category> getQuery()
 */
class CategoryService extends ModelBaseService
{
    public const IMAGE_DIRECTORY   = 'category';
    public const IMAGE_SUFFIX      = '.jpg';

    /**
     * Get FQCN category model
     */
    public function getModel(): string
    {
        return Category::class;
    }

    /**
     * Get list query for AJAX
     */
    public function getListQuery(): Builder
    {
        return $this->getQuery()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->with([
                'children' => static function (Relation $q): void {
                    /** @var Builder<Category> $q */
                    // With children again for policies
                    // cannot check if is second level or more deep - maybe in future?
                    $q->withCount([
                        'children',
                        'terms',
                        'terms as possible_terms' => static function (Builder $query): void {
                            /** @var Builder<Term> $query */
                            $query->possibleLogin();
                        },
                        'terms as total_terms_count' => static function (Builder $query): void {
                            /** @var Builder<Term> $query */
                            $query->withTrashed();
                        },
                    ])->orderBy('position');
                },
            ])
            ->withCount('children');
    }

    /**
     * Get list query for HTML select with children
     */
    public function getHTMLSelectQuery(): Builder
    {
        return $this->getQuery()->whereNull('parent_id')
            ->orderBy('position')
            ->with([
                'children' => static function (Relation $q): void {
                    /** @var Builder<Category> $q */
                    $q->orderBy('position');
                },
            ]);
    }

    public function getAllList(): Builder
    {
        return $this->getQuery()
            ->whereNull('parent_id')
            ->orderBy('position')
            ->with([
                'children' => static function (Relation $q): void {
                    /** @var Builder<Category> $q */
                    $q->orderBy('position');
                },
            ]);
    }

    /**
     * Get list query for categories of with parent ID
     */
    public function getCategoriesQuery(?int $parentId = null): Builder
    {
        return $this->getQuery()->where('parent_id', $parentId)
            ->orderBy('position');
    }

    /**
     * Get query for categories to print on homepage or subpage with parent ID
     */
    public function getHomepageListQuery(?int $parentId = null): Builder
    {
        $query = $this->getCategoriesQuery($parentId);
        if ($parentId === null) {
            $query->has('children', '>', 0);
        } else {
            $query->whereHas('terms', static function (Builder $query): void {
                /** @var Builder<Term> $query */
                $query->possibleLogin();
            });
        }

        return $query;
    }

    /**
     * Return path to image of current category
     */
    public function getImagePath(bool $addFileName = true, bool $absolute = false): string
    {
        $relativePath = \sprintf('%s%s', \baseFolderName(), self::IMAGE_DIRECTORY);
        if ($addFileName) {
            $relativePath .= '/' . $this->getContext()->slug . self::IMAGE_SUFFIX;
        }
        if ($absolute) {
            return UploadStorageService::path($relativePath);
        }

        return $relativePath;
    }

    public function getImageUrl(): ?string
    {
        return UploadStorageService::url($this->getImagePath());

        $url = Cache::get($this->getCacheKey());
        if ($url !== null) {
            return $url;
        }

        $filePath = $this->getImagePath();
        if (UploadStorageService::exists($filePath)) {
            $url = UploadStorageService::url($filePath) . '?v' . UploadStorageService::lastModified($filePath);
            Cache::put($this->getCacheKey(), $url, 120 * 60); // save into cache for 2 hours

            return $url;
        }

        return null;
    }

    protected function getCacheKey(): string
    {
        return 'category-image::' . $this->getImagePath();
    }

    /**
     * Generate slug for given model
     */
    protected function generateSlug(): string
    {
        return \getSlug($this->getContext()->id . '-' . $this->getContext()->name, Category::SLUG_MAX_LENGTH);
    }

    /**
     * Get position for new category in level according to parentId
     */
    protected function getPositionForNewCategory(?int $parentId): int
    {
        return $this->getQuery()->where('parent_id', $parentId)->max('position') + 1;
    }

    /**
     * Save new category and set context
     *
     * @param  array<string, mixed> $data Category data
     * @return Category Created category
     */
    public function insert(array $data): Category
    {
        $coverImg = $data['cover_img'];

        $data['slug'] = \getSlug($data['name'], Category::SLUG_MAX_LENGTH);
        $data['position'] = $this->getPositionForNewCategory($data['parent_id']);
        unset($data['cover_img']);
        $category = $this->getModel()::create($data);
        $this->setContext($category);

        $category->slug = $this->generateSlug();
        $category->save();
        $this->saveImage($coverImg);

        return $category;
    }

    /**
     * Update context category
     *
     * @param array<string, mixed> $data
     */
    public function update(array $data): bool
    {
        $coverImg = $data['cover_img'];

        unset($data['cover_img']);
        $category = $this->getContext();

        // Renaming category change name of image
        // If is new image, remove old one
        $oldImagePath = $this->getImagePath();
        if (!empty($coverImg)) {
            $this->deleteImage();
        }

        $status = $category->update($data);
        $category->slug = $this->generateSlug();
        $category->save();

        // Is new image, save
        if (!empty($coverImg)) {
            $this->saveImage($coverImg);
        } else {
            // Rename image if changed name
            $this->renameImage($oldImagePath);
        }

        return (bool)$status;
    }

    /**
     * Save image to directory, resize and delete old one
     */
    public function saveImage(UploadedFile $imageFile): void
    {
        $tmpDisk = Storage::disk('local');
        $currentPath = $imageFile->store($this->getImagePath(false), 'local');
        $fullPath = $tmpDisk->path($currentPath);
        Resampler::load($fullPath)
            ->crop(600, 335)
            ->save();
        UploadStorageService::writeStream($this->getImagePath(), $tmpDisk->readStream($currentPath));
        $tmpDisk->delete($currentPath);
        Cache::forget($this->getCacheKey());
    }

    /**
     * Rename image if slug is changed
     */
    public function renameImage(string $oldImagePath): void
    {
        if ($oldImagePath === $this->getImagePath()) {
            return;
        }
        if (UploadStorageService::exists($oldImagePath)) {
            UploadStorageService::move($oldImagePath, $this->getImagePath());
        }
    }

    /**
     * Move context category in given direction
     */
    public function move(int $direction): bool
    {
        $current = $this->getContext();
        $operator = $direction < 0 ? '<' : '>';
        /** @var ?Category $sibling */
        $sibling = $this->getQuery()->where('parent_id', $current->parent_id)
            ->where('position', $operator, $current->position)
            ->orderBy('position', $direction < 0 ? 'DESC' : 'ASC')
            ->first();
        if (empty($sibling)) {
            return false;
        }
        $currentPosition = $current->position;
        $current->update(['position' => $sibling->position]);
        $sibling->update(['position' => $currentPosition]);

        return true;
    }

    /**
     * Delete context category
     */
    public function delete(): ?bool
    {
        $this->deleteImage();

        return $this->getContext()->delete();
    }

    /**
     * Delete image of category if exists
     */
    protected function deleteImage(): void
    {
        if (UploadStorageService::exists($this->getImagePath())) {
            UploadStorageService::delete($this->getImagePath());
        }
    }
}
