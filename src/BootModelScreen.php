<?php

declare(strict_types=1);

namespace Orchid\Builder;

use Orchid\Screen\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\Layouts;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Orchid\Builder\Patterns\Model;
use Orchid\Screen\Fields\InputField;
use Illuminate\Http\RedirectResponse;
use Orchid\Builder\Patterns\Migration;

/**
 * Class BootModelScreen.
 */
class BootModelScreen extends Screen
{
    /**
     * Key for cache.
     */
    const MODELS = 'builder.models';

    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Model builder';

    /**
     * Display header description.
     *
     * @var string
     */
    public $description = 'Helps to quickly develop application';

    /**
     * @var string
     */
    public $permission = 'platform.builder';

    /**
     * @var Collection
     */
    public $models;

    /**
     * @var bool
     */
    public $exist = false;

    /**
     * BootModelScreen constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->models = cache(static::MODELS, collect());
    }

    /**
     * Query data.
     *
     * @param string $model
     *
     * @return array
     */
    public function query(string $model = null): array
    {
        if ($model) {
            $this->exist = true;
            $this->name = "Boot for '{$model}' model";
        }

        return [
            'models'        => $this->models,
            'name'          => $model,
            'model'         => $this->models->get($model),
            'fieldTypes'    => Migration::TYPES,
            'relationTypes' => Model::RELATIONS,
        ];
    }

    /**
     * Button commands.
     *
     * @return array
     */
    public function commandBar(): array
    {
        return [

            Link::name(__('Delete'))
                ->icon('icon-trash')
                ->canSee($this->exist)
                ->method('delete'),

            Link::name(__('Build all models'))
                ->icon('icon-magic-wand')
                ->canSee($this->exist)
                ->method('buildModels'),

            Link::name(__('Add new model'))
                ->icon('icon-plus')
                ->modal('CreateModelModal')
                ->title(__('Add new model'))
                ->method('createModel'),
        ];
    }

    /**
     * Views.
     *
     * @return array
     */
    public function layout(): array
    {
        return [
            Layouts::view('builder::index'),
            Layouts::modals([
                'CreateModelModal' => [
                    Layouts::rows([
                        InputField::make('name')
                            ->title(__('Model name:'))
                            ->help(__('Create a new model for your application'))
                            ->pattern('^[a-zA-Z][a-zA-Z0-9-_\.]{1,20}$')
                            ->required(),
                    ]),
                ],
            ]),
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function createModel(Request $request): RedirectResponse
    {
        $name = studly_case($request->get('name'));

        if ($this->models->offsetExists($name)) {
            alert(__('A model with the same name already exists.'));

            return back();
        }

        $this->models->put($name, collect());

        cache()->forever(static::MODELS, $this->models);

        alert(__('Model successfully created.'));

        return redirect()->route('platform.builder.index', $name);
    }

    /**
     * @param string $model
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function delete(string $model): RedirectResponse
    {
        $this->models = $this->models->except($model);
        cache()->forever(static::MODELS, $this->models);

        alert(__('Model has been deleted'));

        return redirect()->route('platform.builder.index');
    }

    /**
     * @param string $model
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function save(string $model, Request $request)
    {
        $attributes = collect($request->except('_token'));
        $this->models->put($model, $attributes);

        cache()->forever(static::MODELS, $this->models);

        return response(200);
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function buildModels()
    {
        foreach ($this->models as $name => $model) {
            $property = [
                'fillable' => [],
                'guarded'  => [],
                'hidden'   => [],
                'visible'  => [],
            ];

            $migration = [];

            $columns = $model->get('columns', []);

            foreach ($columns as $key => $column) {
                if (isset($column['fillable'])) {
                    $property['fillable'][] = $key;
                }
                if (isset($column['guarded'])) {
                    $property['guarded'][] = $key;
                }
                if (isset($column['hidden'])) {
                    $property['hidden'][] = $key;
                }
                if (isset($column['visible'])) {
                    $property['visible'][] = $key;
                }

                $migrate = $column['name'].':'.Migration::TYPES[$column['type']];

                if (isset($column['unique'])) {
                    $migrate .= ':unique';
                }

                if (isset($column['nullable'])) {
                    $migrate .= ':nullable';
                }

                $migration[] = $migrate;
            }

            $model = new Model($name, [
                'property'  => array_filter($property),
                'relations' => $model->get('relations', []),
            ]);

            $model = $model->generate();

            file_put_contents(app_path($name.'.php'), $model);
            Migration::make($name, implode(',', $migration));
        }

        cache()->forget(static::MODELS);

        alert(__('All models have been successfully generated.'));

        return redirect()->route('platform.builder.index');
    }

    /**
     * @param string $model
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getRelated(string $model)
    {
        return view('platform::partials.boot.relatedOption', [
            'name'   => $model,
            'models' => $this->models,
        ]);
    }
}
