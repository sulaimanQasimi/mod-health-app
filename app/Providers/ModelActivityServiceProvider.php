<?php

namespace App\Providers;

use App\Services\ModelActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class ModelActivityServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! config('activitylog.enabled', true)) {
            return;
        }

        foreach ($this->discoverApplicationModels() as $modelClass) {
            foreach (['created', 'updated', 'deleted'] as $event) {
                Event::listen("eloquent.{$event}: {$modelClass}", function (Model $model) use ($event) {
                    app(ModelActivityLogger::class)->log($model, $event);
                });
            }
        }
    }

    /**
     * @return list<class-string<Model>>
     */
    private function discoverApplicationModels(): array
    {
        $models = [];
        $path = app_path('Models');

        if (! is_dir($path)) {
            return $models;
        }

        /** @var SplFileInfo $file */
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relative = str_replace(
                [$path.DIRECTORY_SEPARATOR, '.php'],
                ['', ''],
                $file->getPathname()
            );

            $class = 'App\\Models\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (! class_exists($class)) {
                continue;
            }

            if (! is_subclass_of($class, Model::class)) {
                continue;
            }

            $models[] = $class;
        }

        sort($models);

        return $models;
    }
}
