<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Agent\DashboardController as AgentDashboard;
use App\Http\Controllers\Agent\ConversationController;
use App\Http\Controllers\Admin\TenantController;
use App\Models\User;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\RevenueController;

// Public route
Route::view('/', 'welcome')->name('home');

// Auth routes
require __DIR__ . '/auth.php';

// Dashboard redirect after login
Route::get('/dashboard', function () {
    if (auth()->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('agent.dashboard');
})->middleware(['auth'])->name('dashboard');

// Admin routes
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboard::class, 'index'])
            ->name('admin.dashboard');
        Route::get('/tenants', [TenantController::class, 'index'])
            ->name('admin.tenants');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])
            ->name('admin.tenants.show');
        Route::post('/tenants/{tenant}/toggle', [TenantController::class, 'toggle'])
            ->name('admin.tenants.toggle');
        Route::get('/plans', [PlanController::class, 'index'])->name('admin.plans');
        Route::get('/revenue', [RevenueController::class, 'index'])->name('admin.revenue');
    });

// Agent routes
Route::prefix('agent')
    ->middleware(['auth', 'agent', 'subscription'])
    ->group(function () {
        Route::get('/dashboard', [AgentDashboard::class, 'index'])
            ->name('agent.dashboard');

        Route::get('/analytics', [\App\Http\Controllers\Agent\AnalyticsController::class, 'index'])
            ->name('agent.analytics');

        Route::get('/canned-responses', [\App\Http\Controllers\Agent\CannedResponseController::class, 'index'])
            ->name('agent.canned-responses');

        Route::post('/canned-responses', [\App\Http\Controllers\Agent\CannedResponseController::class, 'store'])
            ->middleware('not_viewer')
            ->name('agent.canned-responses.store');

        Route::delete('/canned-responses/{cannedResponse}', [\App\Http\Controllers\Agent\CannedResponseController::class, 'destroy'])
            ->middleware('not_viewer')
            ->name('agent.canned-responses.destroy');

        Route::get('/canned-responses-data', [\App\Http\Controllers\Agent\CannedResponseController::class, 'data'])
            ->name('agent.canned-responses.data');

        Route::get('/conversations/data', [ConversationController::class, 'data'])
            ->name('agent.conversations.data');

        Route::get('/conversations', [ConversationController::class, 'index'])
            ->name('agent.conversations');

        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])
            ->name('agent.conversations.show');

        Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'getMessages'])
            ->name('agent.conversations.messages');

        Route::post('/conversations/{conversation}/reply', [ConversationController::class, 'reply'])
            ->middleware('not_viewer')
            ->name('agent.conversations.reply');

        Route::post('/conversations/{conversation}/messages/{message}/edit', [ConversationController::class, 'editMessage'])
            ->middleware('not_viewer')
            ->name('agent.messages.edit');

        Route::post('/conversations/{conversation}/messages/{message}/delete', [ConversationController::class, 'deleteMessage'])
            ->middleware('not_viewer')
            ->name('agent.messages.delete');

        Route::post('/conversations/{conversation}/resolve', [ConversationController::class, 'resolve'])
            ->middleware('not_viewer')
            ->name('agent.conversations.resolve');

        Route::post('/conversations/{conversation}/notes', [ConversationController::class, 'addNote'])
            ->middleware('not_viewer')
            ->name('agent.conversations.notes.add');

        Route::delete('/conversations/{conversation}/notes/{note}', [ConversationController::class, 'deleteNote'])
            ->middleware('not_viewer')
            ->name('agent.conversations.notes.delete');

        Route::get('/tags', [\App\Http\Controllers\Agent\TagController::class, 'index'])
            ->name('agent.tags.index');

        Route::post('/tags', [\App\Http\Controllers\Agent\TagController::class, 'store'])
            ->middleware('not_viewer')
            ->name('agent.tags.store');

        Route::delete('/tags/{tag}', [\App\Http\Controllers\Agent\TagController::class, 'destroy'])
            ->middleware('not_viewer')
            ->name('agent.tags.destroy');

        Route::post('/conversations/{conversation}/tags', [\App\Http\Controllers\Agent\TagController::class, 'attach'])
            ->middleware('not_viewer')
            ->name('agent.conversations.tags.attach');

        Route::delete('/conversations/{conversation}/tags/{tag}', [\App\Http\Controllers\Agent\TagController::class, 'detach'])
            ->middleware('not_viewer')
            ->name('agent.conversations.tags.detach');

        Route::post('/conversations/{conversation}/assign', [ConversationController::class, 'assign'])
            ->middleware('not_viewer')
            ->name('agent.conversations.assign');

        Route::post('/conversations/{conversation}/block', [ConversationController::class, 'blockVisitor'])
            ->middleware('not_viewer')
            ->name('agent.conversations.block');

        Route::post('/conversations/{conversation}/unblock', [ConversationController::class, 'unblockVisitor'])
            ->middleware('not_viewer')
            ->name('agent.conversations.unblock');

        Route::get('/password/change', [\App\Http\Controllers\Agent\PasswordController::class, 'edit'])
            ->name('agent.password.change');
        Route::post('/password/change', [\App\Http\Controllers\Agent\PasswordController::class, 'update'])
            ->name('agent.password.update');

        Route::post('/profile/avatar', [\App\Http\Controllers\Agent\PasswordController::class, 'updateAvatar'])
            ->name('agent.profile.avatar');
        Route::delete('/profile/avatar', [\App\Http\Controllers\Agent\PasswordController::class, 'removeAvatar'])
            ->name('agent.profile.avatar.remove');

        // Owner only routes
        Route::get('/embed', [ConversationController::class, 'embed'])
            ->middleware('owner')
            ->name('agent.embed');

        Route::post('/widget/update', [ConversationController::class, 'updateWidget'])
            ->middleware('owner')
            ->name('agent.widget.update');

        Route::post('/widget/business-hours', [ConversationController::class, 'updateBusinessHours'])
            ->middleware('owner')
            ->name('agent.widget.business-hours');

        Route::get('/agents', [ConversationController::class, 'agents'])
            ->middleware('owner')
            ->name('agent.agents');

        Route::post('/agents', [ConversationController::class, 'addAgent'])
            ->middleware('owner')
            ->name('agent.agents.add');

        Route::delete('/agents/{user}', [ConversationController::class, 'deleteAgent'])
            ->middleware('owner')
            ->name('agent.agents.delete');

        Route::patch('/agents/{user}/role', [ConversationController::class, 'updateAgentRole'])
            ->middleware('owner')
            ->name('agent.agents.role');
			
		Route::post('/ping', function() {
			return response()->json(['ok' => true]);
		})->name('agent.ping');

		// Agent file upload — web route taaki session auth + CSRF sahi kaam kare
		Route::post('/upload', [
			\App\Http\Controllers\Api\WidgetController::class,
			'uploadAgentFile'
		])->middleware('not_viewer')->name('agent.upload');
    });
	
	// ── Billing / Stripe Routes ──

Route::middleware(['auth'])->group(function () {
    Route::get('/pricing',         [App\Http\Controllers\StripeController::class, 'pricing'])->name('billing.pricing');
    Route::post('/billing/checkout', [App\Http\Controllers\StripeController::class, 'checkout'])->name('billing.checkout');
    Route::get('/billing/success', [App\Http\Controllers\StripeController::class, 'success'])->name('billing.success');
    Route::get('/billing',         [App\Http\Controllers\StripeController::class, 'billing'])->name('billing.dashboard');
    Route::post('/billing/cancel', [App\Http\Controllers\StripeController::class, 'cancel'])->name('billing.cancel');
});

// Webhook — AUTH se bahar hona chahiye
Route::post('/stripe/webhook', [App\Http\Controllers\StripeController::class, 'webhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);