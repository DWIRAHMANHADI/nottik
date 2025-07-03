<?php
file_put_contents(__DIR__.'/testlog.txt', date('c')." | test\n", FILE_APPEND);
echo "OK";