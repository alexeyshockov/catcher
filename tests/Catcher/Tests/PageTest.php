<?php

namespace Catcher\Tests;

use Silex\WebTestCase;

/**
 * @author Alexey Shockov <alexey@shockov.com>
 */
class PageTest extends WebTestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../src/app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $this->createClient()->request('DELETE', '/pages');
    }

    /**
     * @test
     */
    public function notExistingPagesShouldBeNotFound()
    {
        $client = $this->createClient();

        $client->request('GET', '/pages?url='.urlencode('/pages'));

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertSame('', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function existingPagesShouldBeFound()
    {
        $client = $this->createClient();

        $page = serialize(array(
            'url'     => '/companies',
            'tags'    => array('companies'),
            'content' => serialize(array(
                'status' => 200,
                'body'   => 'Some body.',
                'headers' => array(
                    'content-type' => 'text/plain; charset=UTF-8'
                ),
            )),
        ));

        $client->request('POST', '/pages', array(), array(), array(), $page);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/pages?url='.urlencode('/companies'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Some body.', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function clearingShouldBeAvailableByTags()
    {
        $client = $this->createClient();

        $page = serialize(array(
            'url'     => '/companies',
            'tags'    => array('companies'),
            'content' => serialize(array(
                'status' => 200,
                'body'   => 'Some body.',
                'headers' => array(
                    'content-type' => 'text/plain; charset=UTF-8'
                ),
            )),
        ));

        $client->request('POST', '/pages', array(), array(), array(), $page);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/pages?url='.urlencode('/companies'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Some body.', $client->getResponse()->getContent());

        $client->request('DELETE', '/pages?'.http_build_query(array('tags' => array('companies'))));

        $client->request('GET', '/pages?url='.urlencode('/companies'));

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertSame('', $client->getResponse()->getContent());
    }

    /**
     * @test
     */
    public function cachingShouldBeAble()
    {
        $client = $this->createClient();

        $page = serialize(array(
            'url'     => '/companies',
            'tags'    => array('companies'),
            'content' => serialize(array(
                'status' => 200,
                'body'   => 'Some body.',
                'headers' => array(
                    'content-type' => 'text/plain; charset=UTF-8'
                ),
            )),
        ));

        $client->request('POST', '/pages', array(), array(), array(), $page);

        $this->assertSame(201, $client->getResponse()->getStatusCode());

        $client->request('GET', '/pages?url='.urlencode('/companies'));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Some body.', $client->getResponse()->getContent());
        $this->assertTrue($client->getResponse()->headers->has('ETag'));

        $client->request('GET', '/pages?url='.urlencode('/companies'), array(), array(), array(
            'HTTP_IF_MATCH' => $client->getResponse()->headers->get('ETag')
        ));

        $this->assertSame(304, $client->getResponse()->getStatusCode());
        $this->assertSame('', $client->getResponse()->getContent());
    }
}
