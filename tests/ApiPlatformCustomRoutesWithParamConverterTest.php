<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Quick test on all en page that should return at least 200 OK + some other checks
 *
 * Take care : those tests depends on DB for instance, and on parameters.yml (test_MYKEY)
 * We have to use fixtures or SQLITE dbs with required data to make the app run in test mode (or mock everything)
 */
class ApiPlatformCustomRoutesWithParamConverterTest extends HTTP200Abstract
{
    public $bodyOk = <<<JSON
{
    "book": {
        "title": "test from special 3",
        "editors": [{
            "publication_date": "1519664915", 
            "collection": "Hachette collection bis", 
            "isbn": "2-87764-257-7", 
            "editor": {
                "name": "JeanPaul Edition"
            }
        }, {
            "publication_date": "1519747464", 
            "collection": "Ma Tu vue", 
            "isbn": "2-87764-257-7", 
            "editor": {
                "name": "JeanPaul Edition"
            }
        }],
        "authors": [{
            "role": {
                "translation_key": "WRITER"
            }, 
            "author": {
                "firstname": "Marc", 
                "lastname": "Douche"
            }
        }, {
            "role": {
                "translation_key": "DRAWER"
            }, 
            "author": {
                "firstname": "Paul", 
                "lastname": "Smith"
            }
        }],
        "serie": {
            "name": "my Serie Name"
        }
    }
}
JSON;

public $bodyNoEditor = <<<JSON
{
    "book": {
        "title": "test from special 3",
        "editors": [{
            "publication_date": "1519664915", 
            "collection": "Hachette collection bis", 
            "isbn": "2-87764-257-7", 
            "editor": {
                "name": "JeanPaul Edition"
            }
        }, {
            "publication_date": "1519747464", 
            "collection": "Ma Tu vue", 
            "isbn": "2-87764-257-7", 
            "editor": {
            }
        }],
    }
}
JSON;

    /**
     * @group git-pre-push
     */
    public function testBookSpecialSample3WithAllEntitiesToBeCreated()
    {
        $client = $this->getClient();
        $router = $this->getRouter();
        // $uri = $router->generate('book_special_sample3', []);
        // router fails to generate the route so for instance don't loose time and force uri
        $uri = '/api/booksiu/special_3';

        $client->request('POST', $uri, [], [], [], $this->bodyOk);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);

        $this->assertSame(201, $response->getStatusCode());

        // @todo test the json return
        /**
         * {
         *   "id": 1,
         *   "title": "test from special 3",
         *   "description": null,
         *   "indexInSerie": null,
         *   "reviews": [],
         *   "serie": "/api/series/1",
         *   "authors": ["/api/project_book_creation/1", "/api/project_book_creation/2", ],
         *   "editors": ["/api/project_book_creation/1", "/api/project_book_creation/2",]
         * }
         */
        $dbResult = $this->dbCon->fetchAll('SELECT * FROM book WHERE id = ' . $responseData['id']);
        $this->assertCount(1, $dbResult);
        $this->assertEquals($responseData['id'], $dbResult[0]['id']);
        $this->assertEquals("test from special 3", $responseData['title']);
        $this->assertTrue(strpos($responseData['serie_id'], $responseData['serie']));

        $this->assertEquals($responseData['title'], $dbResult[0]['title']);

        $dbResult = $this->dbCon->fetchAll('SELECT * FROM project_book_creation WHERE book_id = ' . $responseData['id']);
        $this->assertCount(2, $dbResult);

        $dbResult = $this->dbCon->fetchAll('SELECT * FROM project_book_edition WHERE book_id = ' . $responseData['id']);
        $this->assertCount(2, $dbResult);

        return;
    }

    /**
     * @group git-pre-push
     */
    public function testBookSpecialSample3WithDeduplicationEntityToBeCreated()
    {
        $this->markTestIncomplete('test with 2 identic editor in different edition');

        return;
    }

    /**
     * @group git-pre-push
     */
    public function testBookSpecialSample3WithErrors()
    {
        $this->markTestIncomplete('test with BodyFail');

        return;
    }

    /**
     * @group git-pre-push
     */
    public function testPages()
    {
        $client = $this->getClient();
        $router = $this->getRouter();

        $this->checkPages($router, $client);
    }

    /**
     * Is it really pertinent to test all api ? most of them are managed by ApiPlatform, so we should test only specific
     * api
     *
     * @group git-pre-push
     */
    public function testAPI()
    {
        $this->markTestIncomplete('checkApi is not ok coz it should test only custom routes');

        $client = $this->getClient();
        $router = $this->getRouter();

        $this->checkApi($router, $client);
    }
}