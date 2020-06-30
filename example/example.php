<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../src/Entity/Destination.php';
require_once __DIR__ . '/../src/Entity/Quote.php';
require_once __DIR__ . '/../src/Entity/Site.php';
require_once __DIR__ . '/../src/Entity/Template.php';
require_once __DIR__ . '/../src/Entity/User.php';
require_once __DIR__ . '/../src/Context/ApplicationContext.php';
require_once __DIR__ . '/../src/Repository/Repository.php';
require_once __DIR__ . '/../src/Repository/DestinationRepository.php';
require_once __DIR__ . '/../src/Repository/QuoteRepository.php';
require_once __DIR__ . '/../src/Repository/SiteRepository.php';
require_once __DIR__ . '/../src/TemplateManager.php';
require_once __DIR__ . '/../src/Template/TagsProcessor.php';
require_once __DIR__ . '/../src/Template/QuoteTagsProcessor.php';
require_once __DIR__ . '/../src/Template/UserTagsProcessor.php';

$faker = \Faker\Factory::create();

$currentSite = new Site($faker->randomNumber(), $faker->url);
$currentUser = new User($faker->randomNumber(), $faker->firstName, $faker->lastName, $faker->email);

$applicationContext = new ApplicationContext($currentSite, $currentUser);

$quote = new Quote($faker->randomNumber(), $faker->randomNumber(), $faker->randomNumber(), $faker->date());
$destination = new Destination($faker->randomNumber(), $faker->country, 'en', $faker->slug());

$template = new Template(
    1,
    'Votre livraison à [quote:destination_name]',
    "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Bien cordialement,

L'équipe Convelio.com
");

$quoteTagsProcessor = new QuoteTagsProcessor($applicationContext);
$userTagsProcessor = new UserTagsProcessor($applicationContext);

$templateManager = new TemplateManager([$quoteTagsProcessor, $userTagsProcessor]);

$message = $templateManager->getTemplateComputed($template, [
    'quote' => $quote,
    'destination' => $destination,
]);

echo $message->subject . "\n" . $message->content;
