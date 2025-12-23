<?php

use App\Mcp\Servers\PublicServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('public', PublicServer::class);
Mcp::web('/mcp/public', PublicServer::class);
