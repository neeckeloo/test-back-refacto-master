<?php

final class UserTagsProcessor extends TagsProcessor
{
    private $applicationContext;

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    /**
     * @param string $text
     * @param array $data
     * @return string
     */
    public function process($text, array $data = [])
    {
        $user = (isset($data['user']) && ($data['user'] instanceof User)) ? $data['user'] : $this->applicationContext->getCurrentUser();

        if ($this->hasTag('user:first_name', $text)) {
            $text = $this->replaceTag('user:first_name', ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }
}
