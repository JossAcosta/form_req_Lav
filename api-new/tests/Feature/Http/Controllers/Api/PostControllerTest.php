<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\Post;
use function PHPUnit\Framework\assertJson;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title'     =>'El post de pruebas'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title'   => 'El post de pruebas'])
            ->assertStatus(201);

        $this->assertDatabaseHas('posts', ['title'   => 'El post de pruebas']);
    }

    public function test_validate_title(){
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title' => ''
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('title');
    }

    public function test_show(){
        $post = factory(Post::class)->create();
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET',  "/api/posts/$post->id");
        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title'   =>  $post->title] )
            ->assertStatus(200);
    }

    public function test_404_show(){
        $response = $this->json('GET',  "/api/posts/1000");
        $response->assertStatus(401);
    }

    public function test_update()
    {
        // $this->withoutExceptionHandling();
        $post = factory(Post::class)->create();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", [
            'title'     =>'nuevo'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
            ->assertJson(['title'   => 'nuevo'])
            ->assertStatus(200);

        $this->assertDatabaseHas('posts', ['title'   => 'nuevo']);
    }

    public function test_delete()
    {
        // $this->withoutExceptionHandling();
        $post = factory(Post::class)->create();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['id'   => $post->id]);
    }

    public function test_index(){
        factory(Post::class, 5)->create();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts');
        $response->assertJsonStructure([
            'data'  => [
            '*' => ['id', 'title', 'created_at','updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest(){
        $this->json('GET',  'api/posts')->assertStatus(401);
        $this->json('POST', 'api/posts')->assertStatus(401);
        $this->json('GET',  'api/posts/1000')->assertStatus(401);
        $this->json('PUT',  'api/posts/1000')->assertStatus(401);
        $this->json('DELETE',  'api/posts/100')->assertStatus(401);
    }
}
