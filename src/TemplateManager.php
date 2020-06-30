<?php

class TemplateManager
{
    private $tagsProcessors;

    public function __construct(array $tagsProcessors)
    {
        $this->tagsProcessors = $tagsProcessors;
    }

    public function getTemplateComputed(Template $template, array $data)
    {
        $computed = clone($template);
        $computed->subject = $this->computeText($template->subject, $data);
        $computed->content = $this->computeText($template->content, $data);

        return $computed;
    }

    private function computeText($text, array $data)
    {
        foreach ($this->tagsProcessors as $tagsProcessor) {
            if (!$tagsProcessor instanceof TagsProcessor) {
                throw new \RuntimeException('A tags processor must be an instance of TagsProcessor.');
            }

            $text = $tagsProcessor->process($text, $data);
        }

        return $text;
    }
}
