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
        $this->assertContains("example", $results->entities[0]->alias);
        $this->assertContains("category", $results->entities[0]->alias);
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

    public function testUpdateNotAuth()
    {
        $res = $this->call('PATCH', '/categories/0');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testUpdateNotExists()
    {
        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testUpdateValidateFailure()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'alias' => 'Invalid Alias',
            'parent_id' => 'invalid',
            'order' => 'invalid',
            'status' => 'invalid',
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias format is invalid.', $results->errors->alias[0]);
        $this->assertEquals('The parent id must be a number.', $results->errors->parent_id[0]);
        $this->assertEquals('The order must be a number.', $results->errors->order[0]);
        $this->assertEquals('The status must be a number.', $results->errors->status[0]);
    }

    public function testUpdateWrongParent()
    {
        $category = factory(Category::class)->create();
        $category2 = factory(Category::class)->create(['parent_id' => $category->id]);

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'parent_id' => $category2->id,
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testUpdateWithEmptyName()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => '',
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('validation', $results->type);
        $this->assertObjectHasAttribute('name', $results->errors);
        $this->assertEquals('The name field is required.', $results->errors->name[0]);
    }

    public function testUpdateNothingChange()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($category->name, $results->entities[0]->name);
        $this->assertEquals($category->alias, $results->entities[0]->alias);
        $this->assertEquals($category->description, $results->entities[0]->description);
        $this->assertEquals($category->parent_id, $results->entities[0]->parent->id);
        $this->assertEquals($category->order, $results->entities[0]->order);
        $this->assertEquals($category->status, $results->entities[0]->status);
    }

    public function testUpdateWithNewInformation()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'New Name',
            'alias' => 'new-alias',
            'description' => 'New description',
            'parent_id' => 0,
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('New Name', $results->entities[0]->name);
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals('New description', $results->entities[0]->description);

        // change keep current alias
        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'New Name',
            'alias' => 'new-alias',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('new-alias', $results->entities[0]->alias);
        $this->assertEquals(0, $results->entities[0]->parent->id);
    }

    public function testUpdateWithBlankAlias()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'alias' => '',
        ]);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertNotEquals($category->alias, $results->entities[0]->alias);
    }

    public function testUpdateWithExistsAlias()
    {
        $category = factory(Category::class)->create();
        $otherProduct = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'New Title',
            'alias' => $otherProduct->alias,
        ]);
        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('The alias has already been taken.', $results->errors->alias[0]);
    }

    public function testUpdateWithParentIdNotExists()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'Example Category',
            'parent_id' => 99,
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testUpdateWithParentIdIsSelf()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'Example Category',
            'parent_id' => $category->id,
        ]);

        $this->assertEquals(400, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals('error', $results->status);
        $this->assertEquals('The selected parent id is invalid.', $results->errors->parent_id[0]);
    }

    public function testUpdateWithParentIdExists()
    {
        $category = factory(Category::class)->create();
        $categoryParent = factory(Category::class)->create();

        $user = factory(App\User::class)->make();
        Auth::login($user);

        $res = $this->call('PATCH', '/categories/' . $category->id, [
            'name' => 'Example Category',
            'parent_id' => $categoryParent->id,
            'alias' => ''
        ]);

        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals($categoryParent->id, $results->entities[0]->parent->id);
        $this->assertContains("example", $results->entities[0]->alias);
        $this->assertContains("category", $results->entities[0]->alias);
    }

    public function testReadNotFound()
    {
        $res = $this->call('GET', '/categories/0');

        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testReadFound()
    {
        // test read found with id
        $category = factory(Category::class)->create();

        $res = $this->call('GET', '/categories/' . $category->id);

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($category->name, $results->entities[0]->name);
        $this->assertEquals($category->alias, $results->entities[0]->alias);
        $this->assertEquals($category->description, $results->entities[0]->description);
        $this->assertEquals($category->image, $results->entities[0]->image);
        $this->assertTrue($results->entities[0]->isEnable);

        // test read found with alias
        $category = factory(Category::class)->create(['alias' => 'example-alias']);

        $res = $this->call('GET', '/categories/' . 'example-alias');

        $this->assertEquals(200, $res->getStatusCode());

        $results = json_decode($res->getContent());
        $this->assertObjectHasAttribute('entities', $results);
        $this->assertInternalType('array', $results->entities);
        $this->assertEquals($category->name, $results->entities[0]->name);
        $this->assertEquals($category->alias, $results->entities[0]->alias);
        $this->assertEquals($category->description, $results->entities[0]->description);
        $this->assertEquals($category->image, $results->entities[0]->image);
    }

    public function testEnable()
    {
        // test don't login
        $res = $this->call('POST', '/categories/0/enable');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        // test find not found
        $res = $this->call('POST', '/categories/1/enable');
        $this->assertEquals('404', $res->getStatusCode());

        // test category type has enable
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories/' . $category->id . '/enable');
        $this->assertEquals('204', $res->getStatusCode());

        // test set enable
        $category->disable();
        $res = $this->call('POST', '/categories/' . $category->id . '/enable');
        $this->assertEquals('204', $res->getStatusCode());
        $category = Category::find($category->id);
        $this->assertEquals(true, $category->isEnable());
    }

    public function testDisable()
    {
        $res = $this->call('POST', '/categories/0/disable');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);
        // test find not found
        $res = $this->call('POST', '/categories/1/disable');
        $this->assertEquals('404', $res->getStatusCode());

        // test set disable
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories/' . $category->id . '/disable');
        $this->assertEquals('204', $res->getStatusCode());
        $category = Category::find($category->id);
        $this->assertEquals(false, $category->isEnable());

        // test category type has disable
        $res = $this->call('POST', '/categories/' . $category->id . '/disable');
        $this->assertEquals('204', $res->getStatusCode());
    }

    public function testMoveToTrash()
    {
        // test don't login
        $res = $this->call('POST', '/categories/1/trash');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        // test find not found
        $res = $this->call('POST', '/categories/1/trash');
        $this->assertEquals('404', $res->getStatusCode());

        // test set category is delete
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories/' . $category->id . '/trash');
        $this->assertEquals('204', $res->getStatusCode());
        $exists = Category::find($category->id);
        $this->assertNull($exists);
        $category = Category::onlyTrashed()->where('id', $category->id)->count();
        $this->assertEquals(1, $category);
    }

    public function testRestoreFromTrash()
    {
        // test don't login
        $res = $this->call('POST', '/categories/1/restore');
        $this->assertEquals(401, $res->getStatusCode());

        $user = factory(App\User::class)->make();
        Auth::login($user);

        // test find not found
        $res = $this->call('POST', '/categories/1/restore');
        $this->assertEquals('404', $res->getStatusCode());

        // test restore category
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories/' . $category->id . '/trash');
        $res = $this->call('POST', '/categories/' . $category->id . '/restore');
        $this->assertEquals('204', $res->getStatusCode());
        $exists = Category::find($category->id);
        $this->assertEquals($category->name, $exists->name);
        $existsTrash = Category::onlyTrashed()->count();
        $this->assertEquals(0, $existsTrash);
    }

    public function testDeleteNotAuthAndPermission()
    {
        $res = $this->call('DELETE', '/categories/0');
        $this->assertEquals(401, $res->getStatusCode());
    }

    public function testDeleteNotFound()
    {
        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', '/categories/0');
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testDeleteSuccess()
    {
        $category = factory(Category::class)->create();

        $user = factory(App\User::class)->make([ 'hasRole' => true ]);
        Auth::login($user);

        $res = $this->call('DELETE', "/categories/{$category->id}");
        $this->assertEquals(204, $res->getStatusCode());

        $exists = Category::withTrashed()->where('id', $category->id)->first();
        $this->assertNull($exists);

        // test delete from trash
        $category = factory(Category::class)->create();
        $res = $this->call('POST', '/categories/' . $category->id . '/trash');
        $this->assertEquals(204, $res->getStatusCode());
        $res = $this->call('DELETE', "/categories/{$category->id}");
        $this->assertEquals(204, $res->getStatusCode());
        $exists = Category::withTrashed()->where('id', $category->id)->first();
        $this->assertNull($exists);
    }

    public function testBrowseNotFound()
    {
        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseFound()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(count($categories), count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseWithScroll()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        // 5 items first
        $res = $this->call('GET', '/categories?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseWithPagination()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        // 5 items first
        $res = $this->call('GET', '/categories?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $res = $this->call('GET', '/categories?limit=5&page=2');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $res = $this->call('GET', '/categories?limit=5&page=3');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseWithSort()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create([
                'name' => 9 - $i,
            ]);
        }

        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/categories?sort=name');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[$i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/categories?sort=name&direction=asc');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseWithFilter()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create([
                'name' => 'Test' . $i,
            ]);
        }

        $res = $this->call('GET', '/categories');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));

        $res = $this->call('GET', '/categories?name=Test0');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(1, $results->meta->total);
        $this->assertEquals(1, count($results->entities));

        $res = $this->call('GET', '/categories?name=Test%');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));
    }

    public function testBrowseDraftNotFound()
    {
        $res = $this->call('GET', '/categories/trash');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseDraftFound()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        $user = factory(App\User::class)->make();
        Auth::login($user);

        for ($i = 1; $i <= 10; ++$i) {
            $res = $this->call('POST', '/categories/' . $i . '/trash');
            $this->assertEquals(204, $res->getStatusCode());
        }

        $res = $this->call('GET', '/categories/trash');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(count($categories), count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
            $this->assertObjectHasAttribute('deletedAt', $results->entities[$i]);
        }
    }

    public function testBrowseDraftWithScroll()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        $user = factory(App\User::class)->make();
        Auth::login($user);

        for ($i = 1; $i <= 10; ++$i) {
            $res = $this->call('POST', '/categories/' . $i . '/trash');
            $this->assertEquals(204, $res->getStatusCode());
        }

        // 5 items first
        $res = $this->call('GET', '/categories/trash?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $nextLink = $results->links->next->href;
        $res = $this->call('GET', $nextLink);
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseDraftWithPagination()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create();
        }

        $user = factory(App\User::class)->make();
        Auth::login($user);

        for ($i = 1; $i <= 10; ++$i) {
            $res = $this->call('POST', '/categories/' . $i . '/trash');
            $this->assertEquals(204, $res->getStatusCode());
        }

        // 5 items first
        $res = $this->call('GET', '/categories/trash?limit=5');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        // 5 items next
        $res = $this->call('GET', '/categories/trash?limit=5&page=2');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(5, count($results->entities));
        for ($i = 0; $i < 5; ++$i) {
            $this->assertEquals($categories[4 - $i]->id, $results->entities[$i]->id);
        }

        // over list
        $res = $this->call('GET', '/categories/trash?limit=5&page=3');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(0, count($results->entities));
    }

    public function testBrowseDraftWithSort()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create([
                'name' => 9 - $i,
            ]);
        }

        $user = factory(App\User::class)->make();
        Auth::login($user);

        for ($i = 1; $i <= 10; ++$i) {
            $res = $this->call('POST', '/categories/' . $i . '/trash');
            $this->assertEquals(204, $res->getStatusCode());
        }

        $res = $this->call('GET', '/categories/trash');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/categories/trash?sort=name');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[$i]->id, $results->entities[$i]->id);
        }

        $res = $this->call('GET', '/categories/trash?sort=name&direction=asc');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, count($results->entities));
        for ($i = 0; $i < 10; ++$i) {
            $this->assertEquals($categories[9 - $i]->id, $results->entities[$i]->id);
        }
    }

    public function testBrowseDraftWithFilter()
    {
        $categories = [];
        for ($i = 0; $i < 10; ++$i) {
            $categories[] = factory(Category::class)->create([
                'name' => 'Test' . $i,
            ]);
        }

        $user = factory(App\User::class)->make();
        Auth::login($user);

        for ($i = 1; $i <= 10; ++$i) {
            $res = $this->call('POST', '/categories/' . $i . '/trash');
            $this->assertEquals(204, $res->getStatusCode());
        }

        $res = $this->call('GET', '/categories/trash');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));

        $res = $this->call('GET', '/categories/trash?name=Test0');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(1, $results->meta->total);
        $this->assertEquals(1, count($results->entities));

        $res = $this->call('GET', '/categories/trash?name=Test%');
        $this->assertEquals(200, $res->getStatusCode());
        $results = json_decode($res->getContent());
        $this->assertEquals(10, $results->meta->total);
        $this->assertEquals(10, count($results->entities));
    }
}

