<div data-controller="components--builder">
    @if(count($models))
        <div class="nav-tabs-alt">
            <ul class="nav nav-tabs padder" role="tablist">
                @foreach($models as $name => $value)
                    <li class="nav-item">
                        <a href="{{route('platform.builder.index',$name)}}"
                           class="nav-link {{active(route('platform.builder.index',$name))}}">
                            <i class="icon-folder m-r-xs"></i> {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @isset($model)

        <div class="row padder-v">
            <div class="col-sm-12">

                <div class="wrapper">
                    <div class="container">

                        <div class="form-inline">
                            <div class="form-group mb-2">
                                <div>
                                    {{ __('Columns') }}<br>
                                    <p class="text-muted">{{ __('Determine the columns for your model') }}</p>
                                </div>
                            </div>
                            <div class="form-group mx-sm-3 mb-2">
                                <label class="sr-only">{{ __('Columns') }}</label>
                                <input type="text"
                                       data-target="components--builder.column"
                                       data-action="keypress->components--builder#addColumn"
                                       class="form-control"
                                       placeholder="Column">
                            </div>
                            <button type="button"
                                    data-action="components--builder#addColumn"
                                    class="btn btn-default mb-2">
                                Add Columns
                            </button>
                        </div>
                    </div>
                </div>

                <table class="table m-b-none">
                    <thead>
                    <tr>
                        <th class="text-left" width="35%">Name</th>
                        <th class="text-center" width="50%">Type</th>
                        <th class="text-center" width="5%">Fillable</th>
                        <th class="text-center" width="5%">Guarded</th>
                        <th class="text-center" width="5%">Nullable</th>
                        <th class="text-center" width="5%">Unique</th>
                        <th class="text-center" width="5%">Hidden</th>
                    </tr>
                    </thead>
                    <tbody id="boot-container-column">
                    @foreach($model->get('columns',[]) as $column)
                        @include('builder::column', [
                            'column' => $column,
                            'relationTypes' => $relationTypes,
                           ])
                    @endforeach

                    </tbody>
                </table>

            </div>
        </div>

    @else
        <div class="app-content-center text-center m-b">
            <h3 class="font-thin">
                <i class="icon-table block m-b"></i>
                {{ __('Select or create model') }}
            </h3>
        </div>
    @endisset


    @isset($model)
        <div class="row padder-v">
            <div class="col-sm-12">
                <div class="wrapper">
                    <div class="container">
                        <div class="form-inline">
                            <div class="form-group mb-2">
                                <div>
                                    {{ __('Relationships') }}<br>
                                    <p class="text-muted">{{ __('Determine the relationships for this model') }}</p>
                                </div>
                            </div>
                            <div class="form-group mx-sm-3 mb-2">
                                <label class="sr-only">{{ __('Choose Model') }}:</label>

                                <select
                                        class="form-control w-full"
                                        data-target="components--builder.relation"
                                >
                                    <option>{{ __('Select Model') }}</option>
                                    @foreach($models as $name => $value)
                                        <option value="{{$name}}">{{$name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="button"
                                    data-action="components--builder#addRelation"
                                    class="btn btn-default mb-2">
                                Add Columns
                            </button>
                        </div>
                    </div>
                </div>

                <table class="table m-b-none">
                    <thead>
                    <tr>
                        <th class="text-center">Model</th>
                        <th class="text-center">Relationship Type</th>
                        <th class="text-center">Local Key</th>
                        <th class="text-center">Related Key</th>
                    </tr>
                    </thead>
                    <tbody id="boot-container-relationship">
                    @foreach($model->get('relations',[]) as $relation)
                        @include('builder::relationship', [
                            'model' => $model,
                            'relation' => $relation,
                            'columns' => $model ? $model->get('columns',[]) : [],
                            'relationTypes' => $relationTypes
                        ])
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

</div>


@push('scripts')
    <script type="text/x-tmpl" id="boot-template-column">
        @include('builder::column', [
            'column' => [],
            'fieldTypes' => $fieldTypes
        ])
    </script>

    <script type="text/x-tmpl" id="boot-template-relationship">
        @include('builder::relationship', [
            'relations' => [],
            'columns' => $model ? $model->get('columns',[]) : [],
            'relationTypes' => $relationTypes
        ])
    </script>
@endpush