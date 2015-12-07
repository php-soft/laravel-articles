<?php

use PhpSoft\Articles\Models\Article;
use PhpSoft\Articles\Models\Category;

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

    public function testCreateDoesExitsCategory()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);

        $user = factory(App\User::class)->create();
        Auth::login($user);
        $res = $this->call('POST', '/articles', [
            'title'       => 'Example Article',
            'content'     => 'content',
            'category_id' => 1
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('category_id', $results->errors);
        $this->assertEquals('The selected category id is invalid.', $results->errors->category_id[0]);
        $this->assertEquals('The selected category id is invalid.', $results->message);
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
        $this->assertEquals(1, $results->entities[0]->user->id);
        $this->assertEquals($category->id, $results->entities[0]->category->id);
        $this->assertContains("example", $results->entities[0]->alias);
        $this->assertContains("article", $results->entities[0]->alias);
    }

    public function testUpdateNoAuth()
    {
        $res = $this->call('PATCH', '/articles/1');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testUpdateWrongInput()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('PATCH', '/articles/' . $article->id, [
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

    public function testUpdateDoesExitsCategory()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);

        $res = $this->call('PATCH', '/articles/' . $article->id, [
            'category_id' => 5
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('category_id', $results->errors);
        $this->assertEquals('The selected category id is invalid.', $results->errors->category_id[0]);
        $this->assertEquals('The selected category id is invalid.', $results->message);
    }

    public function testUpdateWithEmptyParam()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);

        $res = $this->call('PATCH', '/articles/' . $article->id, [
            'category_id' => '',
            'title'       => '',
            'content'     => ''
        ]);

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

    public function testUpdateNothingChange()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);

        $res = $this->call('PATCH', '/articles/' . $article->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($article->title, $results->entities[0]->title);
        $this->assertEquals($article->alias, $results->entities[0]->alias);
        $this->assertEquals($article->description, $results->entities[0]->description);
        $this->assertEquals($article->category_id, $results->entities[0]->category->id);
        $this->assertEquals($article->order, $results->entities[0]->order);
        $this->assertEquals($article->status, $results->entities[0]->status);
    }

    public function testUpdateWithNewInformation()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);

        $res = $this->call('PATCH', '/articles/' . $article->id, [
            'title' => 'New Name',
            'alias' => 'new-alias',
            'description' => 'New description',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('New Name', $results->entities[0]->title);
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals('New description', $results->entities[0]->description);

        // change keep current alias
        $res = $this->call('PATCH', '/articles/' . $article->id, [
            'name' => 'New Name',
            'alias' => 'new-alias',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('new-alias', $results->entities[0]->alias);
    }

    public function testUpdateWithBlankAlias()
    {
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();

        $user = factory(App\User::class)->create();
        Auth::login($user);

        $res = $this->call('PATCH', '/articles/' . $article->id, [
            'alias' => '',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertNotEquals($article->alias, $results->entities[0]->alias);
    }

    public function testEnable()
    {
        // test don't login
        $res = $this->call('POST', '/articles/0/enable');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        // test find not found
        $res = $this->call('POST', '/articles/1/enable');
        $this->assertEquals('404', $res->getStatusCode());

        // test article type has enable
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();
        $res = $this->call('POST', '/articles/' . $article->id . '/enable');
        $this->assertEquals('204', $res->getStatusCode());

        // test set enable
        $article->disable();
        $res = $this->call('POST', '/articles/' . $article->id . '/enable');
        $this->assertEquals('204', $res->getStatusCode());
        $article = Article::find($article->id);
        $this->assertEquals(true, $article->isEnable());
    }

    public function testDisable()
    {
        $res = $this->call('POST', '/articles/0/disable');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);
        // test find not found
        $res = $this->call('POST', '/articles/1/disable');
        $this->assertEquals('404', $res->getStatusCode());

        // test set disable
        $category = factory(Category::class)->create();
        $article = factory(Article::class)->create();
        $res = $this->call('POST', '/articles/' . $article->id . '/disable');
        $this->assertEquals('204', $res->getStatusCode());
        $article = Article::find($article->id);
        $this->assertEquals(false, $article->isEnable());

        // test article type has disable
        $res = $this->call('POST', '/articles/' . $article->id . '/disable');
        $this->assertEquals('204', $res->getStatusCode());
    }
}
