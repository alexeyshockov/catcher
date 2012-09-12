<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app->register(new SilexMongo\MongoDbExtension(), array());

$app['db'] = $app['mongodb']->selectCollection('catcher', 'pages');

$app->get('/pages', function(Request $request) use($app) {
    $url = $request->get('url', '');

    if (empty($url)) {
        return new Response('', 404);
    }

    $page = $app['db']->findOne(array('url' => $url));

    if ($page) {
        $content = unserialize($page['content']);

        $response = new Response($content['body'], $content['status'], $content['headers']);

        if (!$response->headers->has('ETag')) {
            $response->headers->set('ETag', md5($response->getContent()));
        }

        // TODO To settings.
        if ($request->headers->has('If-None-Match')) {
            if ($request->headers->get('If-None-Match') == $response->headers->get('ETag')) {
                return new Response('', 304);
            }
        }

        return $response;
    }

    return new Response('', 404);
});

$app->post('/pages', function(Request $request) use($app) {
    $page = unserialize($request->getContent());

    /*
     * array(
     *  'url'     => '',
     *  'content' => '', // Serialized array.
     *  'tags'    => array(),
     * );
     */

    $page['createdAt'] = new \DateTime();

    // TODO Validate.

    $app['db']->remove(array('url' => $page['url']), array('safe' => true));
    $app['db']->insert($page);

    return new Response('', 201);
});

$app->delete('/pages', function(Request $request) use($app) {
    $tags = $request->query->get('tags', array());

    if (empty($tags)) {
        // Remove all.
        $app['db']->remove(array());
    } else {
        $app['db']->remove(array('tags' => array('$in' => $tags)));
    }

    return new Response('', 204);
});

return $app;
