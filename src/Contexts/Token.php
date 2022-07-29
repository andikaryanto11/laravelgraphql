<?php

namespace LaravelGraphQL\Contexts;

use DateTime;
use Illuminate\Http\Request;
use LaravelCommon\App\Repositories\User\TokenRepository;
use LaravelGraphQL\Contexts\AbstractContext;
use App\Entities\User\Token as UserToken;

class Token extends AbstractContext
{

    /**
     *
     * @var TokenRepository
     */
    protected TokenRepository $tokenRepository;

    /**
     * Undocumented function
     *
     * @param TokenRepository $tokenRepository
     */
    public function __construct(
        TokenRepository $tokenRepository
    )
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * 
     * @inheritDoc
     */
    public function make(Request $request, $context)
    {
        $context->userToken = null;

        if($request->hasHeader('Authorization')){
            $authorization = $request->header('Authorization');
            $now = new DateTime();

            $param = [
                'where' => [
                    ['token', '=', $authorization],
                    // ['expired_at', '=', $ninetyDays]
                ]
            ];

            /**
             * @var UserToken $userToken
             */
            $userToken = $this->tokenRepository->findOne($param);

            if(!empty($userToken)){
                $context->userToken = $userToken;
            }
        }
    }
}
