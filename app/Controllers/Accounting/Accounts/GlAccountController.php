<?php

namespace app\Controllers\Accounting\Accounts;

use app\Repositories\Accounting\GlAccountRepository;
use core\Response;

class GlAccountController
{
    private GlAccountRepository $glAccountRepository;

    public function __construct(GlAccountRepository $glAccountRepository)
    {
        $this->glAccountRepository = $glAccountRepository;

    }
    public function showAllGlAccounts()
    {
        $res = $this->glAccountRepository->findAllGlAccounts();

        return Response::json([
            'success'=>true,
            'data' => $res,
            'errors'=>null,

        ], 200);

    }
}