<?php

use CodeIgniter\CLI\CLI;

/** @var string $message */
/** @var string $code */
CLI::error('ERROR: ' . $code);

CLI::write($message);
CLI::newLine();
