<?php

final class QuoteTagsProcessor extends TagsProcessor
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
        $quote = (isset($data['quote']) && $data['quote'] instanceof Quote) ? $data['quote'] : null;
        $destination = (isset($data['destination']) && $data['destination'] instanceof Destination) ? $data['destination'] : null;

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

        if ($destination !== null) {
            $text = $this->replaceQuoteDestinationTags($text, $quote, $destination);
        }

        return $text;
    }

    /**
     * @param string $text
     * @param Quote $quote
     * @param Destination $destination
     * @return string
     */
    private function replaceQuoteDestinationTags($text, Quote $quote, Destination $destination)
    {
        if ($this->hasTag('quote:destination_name', $text)) {
            $text = $this->replaceTag('quote:destination_name', $destination->countryName, $text);
        }
        if ($this->hasTag('quote:destination_link', $text)) {
            $text = $this->replaceTag(
                'quote:destination_link',
                $this->createQuoteDestinationLink($quote, $destination),
                $text
            );
        }

        return $text;
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
