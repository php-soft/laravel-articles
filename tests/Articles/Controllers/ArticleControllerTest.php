<?php

use PhpSoft\Articles\Models\Article;

class ArticleControllerTest extends TestCase
{
    public function testCreateNotAuth()
    {
        $res = $this->call('POST', '/articles');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testCreateNoInput()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('POST', '/articles');
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('title', $results->errors);
        $this->assertEquals('The title field is required.', $results->errors->title[0]);
        $this->assertEquals('The content field is required.', $results->errors->content[0]);
        $this->assertEquals('The category id field is required.', $results->errors->category_id[0]);
        $this->assertEquals('The title field is required.', $results->message);
    }

    public function testCreateWrongInput()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('POST', '/articles', [
            'alias'       => 'This is invalid alias',
            'order'       => 'invalid',
            'status'      => 'invalid',
            'title'       => 'title',
            'content'     => 'example content',
            'category_id' => 'id category'
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertInternalType('array', $results->errors->order);
        $this->assertEquals('The order must be a number.', $results->errors->order[0]);
    }

    public function testCreateSuccess()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);
        $res = $this->call('POST', '/articles', [
            'title'       => 'Example Article',
            'content'     => 'content',
            'category_id' => $category->id
        ]);

        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Article', $results->entities[0]->title);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals(1, $results->entities[0]->user->id);
        $this->assertEquals(0, $results->entities[0]->order);
        $this->assertEquals(1, $results->entities[0]->status);
        $this->assertContains("example", $results->entities[0]->alias);
        $this->assertContains("article", $results->entities[0]->alias);
    }

    public function testCreateExistsAlias()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $article = factory(Article::class)->create();
        $res = $this->call('POST', '/articles', [
            'title' => 'Example article',
            'alias' => $article->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }
}
