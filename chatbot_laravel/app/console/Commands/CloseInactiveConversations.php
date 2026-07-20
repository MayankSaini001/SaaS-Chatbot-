<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversation;

class CloseInactiveConversations extends Command
{
    protected $signature = 'conversations:close-inactive';
    protected $description = 'Warn after 10 min of inactivity and auto-resolve after 15 min';

    public function handle()
    {
        // Backstop for when nobody is actively polling the conversation.
        // All warn/resolve logic lives in one place: Conversation::runInactivityCheck().
        Conversation::where('status', 'open')
            ->get()
            ->each(function (Conversation $conv) {
                $conv->runInactivityCheck();
            });

        $this->info('Inactivity check complete.');
    }
}
