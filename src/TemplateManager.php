<?php

class TemplateManager
{
    private $applicationContext;

    public function __construct(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    public function getTemplateComputed(Template $tpl, array $data)
    {
        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $user = (isset($data['user']) && ($data['user'] instanceof User)) ? $data['user'] : $this->applicationContext->getCurrentUser();
        $quote = (isset($data['quote']) && $data['quote'] instanceof Quote) ? $data['quote'] : null;
        $destination = (isset($data['destination']) && $data['destination'] instanceof Destination) ? $data['destination'] : null;

        if ($quote !== null) {
            if ($this->hasTag('quote:summary_html', $text)) {
                $text = $this->replaceTag(
                    'quote:summary_html',
                    $this->renderQuoteAsHtml($quote),
                    $text
                );
            }
            if ($this->hasTag('quote:summary', $text)) {
                $text = $this->replaceTag(
                    'quote:summary',
                    $this->renderQuoteAsText($quote),
                    $text
                );
            }

            if ($destination) {
                $text = $this->replaceTag('quote:destination_name', $destination->countryName, $text);
                $text = $this->replaceTag(
                    'quote:destination_link',
                    $this->createQuoteDestinationLink($quote, $destination),
                    $text
                );
            }
        }

        if ($this->hasTag('user:first_name', $text)) {
            $text = $this->replaceTag('user:first_name', ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }

    /**
     * @param string $name
     * @param string $text
     * @return bool
     */
    private function hasTag($name, $text)
    {
        return strpos($text, sprintf('[%s]', $name)) !== false;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param string $text
     * @return string
     */
    private function replaceTag($name, $value, $text)
    {
        return str_replace(sprintf('[%s]', $name), $value, $text);
    }

    private function renderQuoteAsHtml(Quote $quote)
    {
        return '<p>' . $quote->id . '</p>';
    }

    private function renderQuoteAsText(Quote $quote)
    {
        return (string) $quote->id;
    }

    /**
     * @param Quote $quote
     * @param Destination $destination
     * @return string
     */
    private function createQuoteDestinationLink(Quote $quote, Destination $destination)
    {
        $site = $this->applicationContext->getCurrentSite();

        return $site->url . '/' . $destination->countryName . '/quote/' . $quote->id;
    }
}
