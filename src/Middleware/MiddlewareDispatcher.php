<?php

namespace Ntriga\PimcoreSeoBundle\Middleware;

use Ntriga\PimcoreSeoBundle\Model\SeoMetaDataInterface;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    protected array $middlewareAdapterStack;
    protected array $middleware;
    protected array $tasks;

    public function __construct()
    {
        $this->middleware = [];
        $this->middlewareAdapterStack = [];
        $this->tasks = [];
    }

    public function registerMiddlewareAdapter(string $identifier, MiddlewareAdapterInterface $middlewareAdapter): void
    {
        $this->middlewareAdapterStack[$identifier] = $middlewareAdapter;
    }

    public function buildMiddleware(string $identifier, SeoMetaDataInterface $seoMetaData): MiddlewareInterface
    {
        if  (!isset($this->middlewareAdapterStack[$identifier])){
            throw new \Exception(sprintf('SEO MetaData middleware "%s" not registered.', $identifier));
        }

        if (isset($this->middleware[$identifier])){
            return $this->middleware[$identifier];
        }

        $this->middlewareAdapterStack[$identifier]->boot();
        $this->middleware[$identifier] = new Middleware($identifier, $this);

        return $this->middleware[$identifier];
    }

    public function registerTask(callable $callback, string $identifier): void
    {
        if (!is_callable($callback)){
            return;
        }

        $this->tasks[] = [
            'identifier' => $identifier,
            'callback' => $callback
        ];
    }

    public function dispatchTasks(SeoMetaDataInterface $seoMetaData): void
    {
        foreach ($this->tasks as $immediateTask){
            $middlewareAdapter = $this->middlewareAdapterStack[$immediateTask['identifier']];
            call_user_func_array($immediateTask['callback'], array_merge([$seoMetaData], $middlewareAdapter->getTaskArguments()));
        }

        // Reset
        $this->tasks = [];
    }

    public function dispatchMiddlewareFinisher(SeoMetaDataInterface $seoMetaData): void
    {
        foreach ($this->middleware as $identifier => $middleware){
            $middlewareAdapter = $this->middlewareAdapterStack[$identifier];
            $middlewareAdapter->onFinish($seoMetaData);
        }
    }
}
