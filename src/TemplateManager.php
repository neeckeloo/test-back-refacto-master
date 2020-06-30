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
        $user = (isset($data['user']) and ($data['user']  instanceof User))  ? $data['user']  : $this->applicationContext->getCurrentUser();

        if ($quote) {
            $destinationOfQuote = $this->destinationRepository->getById($quote->destinationId);

            if (strpos($text, '[quote:destination_link]') !== false) {
                $destination = $this->destinationRepository->getById($quote->destinationId);
            }

            $containsSummaryHtml = strpos($text, '[quote:summary_html]');
            $containsSummary = strpos($text, '[quote:summary]');

            if ($containsSummaryHtml !== false || $containsSummary !== false) {
                if ($containsSummaryHtml !== false) {
                    $text = str_replace(
                        '[quote:summary_html]',
                        Quote::renderHtml($quote),
                        $text
                    );
                }
                if ($containsSummary !== false) {
                    $text = str_replace(
                        '[quote:summary]',
                        Quote::renderText($quote),
                        $text
                    );
                }
            }

            if (strpos($text, '[quote:destination_name]') !== false) {
                $text = str_replace('[quote:destination_name]', $destinationOfQuote->countryName, $text);
            }

            if (isset($destination)) {
                $text = str_replace(
                    '[quote:destination_link]',
                    $site->url . '/' . $destination->countryName . '/quote/' . $quote->id,
                    $text
                );
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }
        }

        if (strpos($text, '[user:first_name]') !== false) {
            $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($user->firstname)), $text);
        }

        return $text;
    }
}
