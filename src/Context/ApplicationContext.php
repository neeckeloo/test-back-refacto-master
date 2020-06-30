<?php

class ApplicationContext
{
    /**
     * @var Site
     */
    private $currentSite;

    /**
     * @var User
     */
    private $currentUser;

    public function __construct(Site $currentSite, User $currentUser)
    {
        $this->currentSite = $currentSite;
        $this->currentUser = $currentUser;
    }

    public function getCurrentSite()
    {
        return $this->currentSite;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }
}
