<?php

class TemplateManager
{
    private $applicationContext;

    private $destinationRepository;

    public function __construct(
        ApplicationContext $applicationContext,
        DestinationRepository $destinationRepository
    ) {
        $this->applicationContext = $applicationContext;
        $this->destinationRepository = $destinationRepository;
    }

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $site = $this->applicationContext->getCurrentSite();

        $quote = (isset($data['quote']) and $data['quote'] instanceof Quote) ? $data['quote'] : null;
        $user = (isset($data['user']) and ($data['user'] instanceof User)) ? $data['user'] : $this->applicationContext->getCurrentUser();

        if ($quote) {
            $destinationOfQuote = $this->destinationRepository->getById($quote->destinationId);

            if ($this->hasTag('quote:destination_link', $text)) {
                $destination = $this->destinationRepository->getById($quote->destinationId);
            }

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

            if ($this->hasTag('quote:destination_name', $text)) {
                $text = $this->replaceTag('quote:destination_name', $destinationOfQuote->countryName, $text);
            }

            if (isset($destination)) {
                $text = $this->replaceTag(
                    'quote:destination_link',
                    $site->url . '/' . $destination->countryName . '/quote/' . $quote->id,
                    $text
                );
            } else {
                $text = $this->replaceTag('quote:destination_link', '', $text);
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
}
