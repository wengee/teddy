#!/usr/bin/env php
<?php

Phar::mapPhar();
include 'phar://' . __FILE__ . '/{INDEX_FILE}';

__HALT_COMPILER();
