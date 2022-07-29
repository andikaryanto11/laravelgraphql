<?php

namespace LaravelGraphQL;

use GraphQL\Type\Definition\ResolveInfo;

abstract class AbstractResolver
{

    protected $source;
    protected array $args;
    protected $context;
    protected ResolveInfo $info;

    /**
     * Get the value of source
     */ 
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set the value of source
     *
     * @return  self
     */ 
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get the value of args
     * @return array
     */ 
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Set the value of args
     *
     * @return  self
     */ 
    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }

    /**
     * Get the value of context
     */ 
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the value of context
     *
     * @return  self
     */ 
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the value of info
     * 
     * @return ResolveInfo
     */ 
    public function getInfo(): ResolveInfo
    {
        return $this->info;
    }

    /**
     * Set the value of info
     *
     * @return  self
     */ 
    public function setInfo(ResolveInfo $info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Validate Token
     *
     * @throws GraphQLException
     * @return void
     */
    public function validateToken(){

        $context = $this->getContext();
        if(is_null($context->userToken)){
            throw new GraphQLException('You are not authorized');
        }

        if($context->userToken->isExpired()){
            throw new GraphQLException('Token Expired');
        }
    }
}
