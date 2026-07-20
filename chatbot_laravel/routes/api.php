<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WidgetController;

// ── Public widget routes ──
Route::post('/widget/conversation/start', [WidgetController::class, 'startConversation']);
Route::post('/widget/visitor-info',       [WidgetController::class, 'submitVisitorInfo']);
Route::post('/widget/message/send',       [WidgetController::class, 'sendMessage']);
Route::post('/widget/message/edit',       [WidgetController::class, 'editMessage']);
Route::post('/widget/message/delete',     [WidgetController::class, 'deleteMessage']);
Route::post('/widget/upload',             [WidgetController::class, 'uploadFile']);
Route::get('/widget/messages/{conversationId}', [WidgetController::class, 'getMessages']);
Route::get('/widget/settings',            [WidgetController::class, 'getSettings']);
Route::post('/widget/typing',             [WidgetController::class, 'visitorTyping']);
Route::post('/widget/rating',             [WidgetController::class, 'submitRating']);

// ── Agent API routes (session auth via cookie — no sanctum needed) ──
Route::post('/agent/typing',       [WidgetController::class, 'agentTyping']);
Route::post('/agent/upload',       [WidgetController::class, 'uploadAgentFile']);
Route::post('/agent/message/send', [WidgetController::class, 'agentReply']);
