<?php


use Slim\App;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Ramsey\Uuid\Uuid;

require __DIR__ . '/../vendor/autoload.php';

(new class
{
    public function main(Request $request, Response $response)
    {
        return $response->write('Welcome');
    }

    public function get(Request $request, Response $response, $params)
    {
        $id = $params['id'];

        if (Uuid::isValid($id)) {
            $path = sprintf(__DIR__ . '/storage/%s', $id);

            if (file_exists($path))
                return $response->write(file_get_contents($path));
        }

        return $response->withStatus(404)->withJson(['status' => 404, 'result' => 'Not Found']);
    }

    public function create(Request $request, Response $response)
    {
        $content = $request->getParam('c');
        $uuid    = Uuid::uuid1()->toString();
        $result  = file_put_contents(sprintf(__DIR__ . '/storage/%s', $uuid), $content);

        return $result === false ? $response->withJson(['status' => 500, 'result' => false]) :
            $response->withJson(['status' => 200, 'result' => sprintf('%s', $uuid)]);
    }

    public function run()
    {
        $container = new Container();
        $app       = new App($container);

        $app->get('/', [$this, 'main']);
        $app->get('/{id}', [$this, 'get']);
        $app->post('/gist', [$this, 'create']);

        $app->run();
    }

})->run();
