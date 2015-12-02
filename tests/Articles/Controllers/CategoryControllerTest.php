<?php

use PhpSoft\Articles\Models\Category;

class CategoryControllerTest extends TestCase
{
    public function testCreateNotAuth()
    {
        $res = $this->call('POST', '/categories');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testCreateValidateFailure()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('POST', '/categories', [
            'alias' => 'This is invalid alias',
            'parent_id' => 'invalid',
            'order' => 'invalid',
            'status' => 'invalid',
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('name', $results->errors);
        $this->assertEquals('The name field is required.', $results->errors->name[0]);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertInternalType('array', $results->errors->parent_id);
        $this->assertEquals('The parent id must be a number.', $results->errors->parent_id[0]);
        $this->assertInternalType('array', $results->errors->order);
        $this->assertEquals('The order must be a number.', $results->errors->order[0]);
        $this->assertInternalType('array', $results->errors->status);
        $this->assertEquals('The status must be a number.', $results->errors->status[0]);
    }

    public function testCreateSuccess()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'parent_id' => 0,
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals('Example Category', $results->entities[0]->name);
        $this->assertEquals(null, $results->entities[0]->description);
        $this->assertEquals(null, $results->entities[0]->image);
        $this->assertEquals(0, $results->entities[0]->parent->id);
        $this->assertEquals(0, $results->entities[0]->order);
        $this->assertEquals(1, $results->entities[0]->status);
    }

    public function testCreateExistsAlias()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'alias' => $category->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('alias', $results->errors);
        $this->assertInternalType('array', $results->errors->alias);
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testCreateWithParentIdNotExists()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'parent_id' => 1,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testCreateWithParentIdExists()
    {
        $categoryParent = factory(Category::class)->create();
        $user = factory(App\User::class)->make();
        Auth::login($user);
        $res = $this->call('POST', '/categories', [
            'name' => 'Example Category',
            'parent_id' => $categoryParent->id,
        ]);
        $this->assertEquals(201, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($categoryParent->id, $results->entities[0]->parent->id);
    }
}
