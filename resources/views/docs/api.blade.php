@extends('../layout/' . $layout)

@section('subhead')
    <title>API Documentation - AEG</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">API Documentation</h2>
    </div>

    <div class="intro-y grid grid-cols-12 gap-6 mt-5">
        @foreach ($apiRoutes as $route)
            <div class="col-span-12 intro-y">
                <div class="box">
                    <div class="flex flex-col sm:flex-row items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                        <div class="mr-auto">
                            @foreach ($route['methods'] as $method)
                                @if ($method != 'HEAD')
                                    @php
                                        $method_color = '';
                                        switch ($method) {
                                            case 'GET': $method_color = 'bg-success/20 text-success'; break;
                                            case 'POST': $method_color = 'bg-warning/20 text-warning'; break;
                                            case 'PUT': $method_color = 'bg-primary/20 text-primary'; break;
                                            case 'DELETE': $method_color = 'bg-danger/20 text-danger'; break;
                                            default: $method_color = 'bg-slate-200 text-slate-600';
                                        }
                                    @endphp
                                    <span class="font-medium px-2 py-1 rounded {{ $method_color }}">{{ $method }}</span>
                                @endif
                            @endforeach
                            <span class="font-mono text-base ml-3">{{ $route['uri'] }}</span>
                        </div>
                    </div>
                    <div class="p-5">
                        <h3 class="font-medium">Controller Action:</h3>
                        <p class="font-mono text-slate-600 dark:text-slate-500">{{ $route['action'] }}</p>

                        <h3 class="font-medium mt-4">Middleware:</h3>
                        <p>
                            @foreach ($route['middleware'] as $middleware)
                                <span class="text-xs py-1 px-2 rounded-full bg-slate-200 dark:bg-darkmode-400 text-slate-600 dark:text-slate-300 mr-1">{{ $middleware }}</span>
                            @endforeach
                        </p>

                        @if (in_array('POST', $route['methods']) || in_array('PUT', $route['methods']))
                            <h3 class="font-medium mt-4">Example Request Body:</h3>
                            <pre class="bg-slate-100 dark:bg-darkmode-400 rounded-md p-4 overflow-auto">
                                <code class="text-xs">
                                {
                                    "key": "value",
                                    "another_key": "another_value"
                                }
                                </code>
                            </pre>
                        @endif

                        <h3 class="font-medium mt-4">Example Success Response (JSON):</h3>
                        <pre class="bg-slate-100 dark:bg-darkmode-400 rounded-md p-4 overflow-auto">
                            <code class="text-xs">
                            {
                                "status": "success",
                                "data": {
                                    "id": 1,
                                    "name": "Example Data"
                                }
                            }
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
