<?php

namespace Tests\Feature;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminQuizCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_create_quiz(): void
    {
        $this->actingAs($this->admin);

        Livewire::test('admin.quiz-editor')
            ->set('title', 'Test Assessment')
            ->set('description', 'Test Description')
            ->set('type', 'assessment')
            ->set('duration_minutes', 10)
            ->set('is_published', true)
            ->set('questions', [
                ['question' => 'Question 1?', 'options' => ['Option A', 'Option B', 'Option C', 'Option D'], 'correct' => 0],
            ])
            ->call('save')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('quizzes', ['title' => 'Test Assessment']);
        $this->assertDatabaseHas('quizzes', ['title' => 'Test Assessment', 'created_by' => $this->admin->id]);
        $this->assertDatabaseHas('questions', ['question' => 'Question 1?']);
        $this->assertDatabaseHas('options', ['label' => 'Option A']);
    }

    public function test_admin_can_edit_quiz(): void
    {
        $this->actingAs($this->admin);

        $quiz = Quiz::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'description' => 'Original',
            'type' => 'quiz',
            'is_published' => false,
        ]);

        $q = $quiz->questions()->create(['question' => 'Q1?', 'position' => 1, 'points' => 1]);
        $q->options()->create(['label' => 'A1', 'value' => 1, 'is_correct' => true, 'position' => 0]);
        $q->options()->create(['label' => 'A2', 'value' => 2, 'is_correct' => false, 'position' => 1]);

        Livewire::test('admin.quiz-editor', ['quiz' => $quiz])
            ->set('title', 'Updated Title')
            ->set('description', 'Updated Description')
            ->call('save')
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('quizzes', ['title' => 'Updated Title', 'slug' => 'original-title']);
    }

    public function test_admin_can_toggle_publish_status(): void
    {
        $this->actingAs($this->admin);

        $quiz = Quiz::create([
            'title' => 'Test',
            'slug' => 'test-toggle',
            'description' => 'Test',
            'type' => 'assessment',
            'is_published' => false,
        ]);

        Livewire::test('admin.dashboard')
            ->call('toggle', $quiz->id);

        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'is_published' => true]);
    }

    public function test_admin_can_delete_quiz(): void
    {
        $this->actingAs($this->admin);
        $quiz = Quiz::create(['title' => 'Delete me', 'slug' => 'delete-me', 'description' => 'Test', 'type' => 'quiz']);

        Livewire::test('admin.dashboard')->call('delete', $quiz->id);

        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    public function test_quiz_list_shows_only_published_on_frontend(): void
    {
        Quiz::create(['title' => 'Published 1', 'slug' => 'pub-1', 'description' => 'D', 'type' => 'quiz', 'is_published' => true]);
        Quiz::create(['title' => 'Draft 1', 'slug' => 'draft-1', 'description' => 'D', 'type' => 'quiz', 'is_published' => false]);
        Quiz::create(['title' => 'Published 2', 'slug' => 'pub-2', 'description' => 'D', 'type' => 'assessment', 'is_published' => true]);

        $livewire = Livewire::test('quiz-list');

        $quizzes = $livewire->viewData('quizzes');
        $this->assertCount(2, $quizzes);
        $this->assertTrue($quizzes->every(fn($q) => $q->is_published));
    }
}
