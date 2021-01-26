<?php

namespace App\Models\Helpers;

class ScrapeState
{
    protected string    $url;
    protected array     $domTargets = [];
    protected string    $result;
    protected string    $name;
    protected ?array    $info;
    protected string    $html;
    protected array     $links;
    protected int       $attempts = 0;

    public function __construct(string $url, array $domTargets, string $name, ?array $info = [])
    {
        $this->url          = $url;
        $this->domTargets   = $domTargets;
        $this->name         = $name;
        $this->info         = $info;
    }

    /**
     * Get the value of domTargets
     */ 
    public function domElm(): array
    {
        return $this->domTargets;
    }


    /**
     * Get the value of url
     */ 
    public function url()
    {
        return $this->url;
    }

    /**
     * Get the value of name
     */ 
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the value of info
     * @return @string
     */ 
    public function getData(string $key): string
    {
        return $this->info[$key] ?? '';
    }
    
    /**
     * Set the value of info
     *
     * @return  self
     */ 
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get the value of html
     */ 
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set the value of html
     *
     * @return  self
     */ 
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Get the value of links
     */ 
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Set the value of links
     *
     * @return  self
     */ 
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }

    public function requestFailed(): void
    {
        ++$this->attempts;
    }

    public function getFailedCount(): int
    {
        return $this->attempts;
    }

    // public function resetAttepmts(): void
    // {
    //     $this->attempts = 0;
    // }

}
