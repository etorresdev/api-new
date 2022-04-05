<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\User;
use App\Post;

class PostControllerTest extends TestCase
{

    /**
     * - Crear la prueba que queremos testear, obtendremos rojo
     * - Creamos el código para obtener verde
     * - Se refactoriza - Mejorar el codigo sin alterar el resultado
     */

    use RefreshDatabase;

    public function test_store()
    {
        // $this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        //actingAs($user, 'api')----> Permite loguearse con acceso de token api
        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title' => 'Post de prueba'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => 'Post de prueba'])
        ->assertStatus(201); //Ok y se crea un recurso

        $this->assertDatabaseHas('posts', ['title' => 'Post de prueba']);
    }

    public function test_validate_title()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('POST', '/api/posts', [
            'title' => ''
        ]);

        //Status 422: Solicitud bien hecha pero fue imposible completarla
        $response->assertStatus(422)
        ->assertJsonValidationErrors('title');
    }

    public function test_show()
    {
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/api/posts/$post->id");

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => $post->title])
        ->assertStatus(200); //Ok
    }  

    public function test_404_show()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts/1000');

        $response->assertStatus(404); //Ok
    } 

    public function test_update()
    {
        //$this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('PUT', "/api/posts/$post->id", [
            'title' => 'Nuevo post de prueba'
        ]);

        $response->assertJsonStructure(['id', 'title', 'created_at', 'updated_at'])
        ->assertJson(['title' => 'Nuevo post de prueba'])
        ->assertStatus(200); //Ok

        $this->assertDatabaseHas('posts', ['title' => 'Nuevo post de prueba']);
    }

    public function test_delete()
    {
        //$this->withoutExceptionHandling();
        $user = factory(User::class)->create();

        $post = factory(Post::class)->create();

        $response = $this->actingAs($user, 'api')->json('DELETE', "/api/posts/$post->id");

        $response->assertSee(null)
        ->assertStatus(204); //Sin contenido

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_index() 
    {
        //Crear varios Post
        $user = factory(User::class)->create();

        factory(Post::class, 5)->create();
        
        //Acceder al listado de post
        $response = $this->actingAs($user, 'api')->json('GET', '/api/posts');

        //Confirmar que estoy recibiendo lo que espero
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 
                    'title', 
                    'created_at', 
                    'updated_at']
            ]
        ])->assertStatus(200);
    }

    public function test_guest()
    {
        $this->json('GET', '/api/posts')->assertStatus(401); //401, no estamos autorizados al acceso
        $this->json('POST', '/api/posts')->assertStatus(401); //401, no estamos autorizados al acceso
        $this->json('GET', '/api/posts/1000')->assertStatus(401); //401, no estamos autorizados al acceso
        $this->json('PUT', '/api/posts/1000')->assertStatus(401); //401, no estamos autorizados al acceso
        $this->json('DELETE', '/api/posts/1000')->assertStatus(401); //401, no estamos autorizados al acceso
    }
}
