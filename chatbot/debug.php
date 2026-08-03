<?php
$result = shell_exec('grep -rn "agent/conversations" /home4/topscbtk/knights.topscripts.in/chatbot_laravel/resources/ 2>&1');
echo "<pre>$result</pre>";