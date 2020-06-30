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

$faker = \Faker\Factory::create();

$currentSite = new Site($faker->randomNumber(), $faker->url);
$currentUser = new User($faker->randomNumber(), $faker->firstName, $faker->lastName, $faker->email);

$applicationContext = new ApplicationContext($currentSite, $currentUser);
$siteRepository = new SiteRepository();
$destinationRepository = new DestinationRepository();

$template = new Template(
    1,
    'Votre livraison à [quote:destination_name]',
    "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Bien cordialement,

L'équipe Convelio.com
");
$templateManager = new TemplateManager($applicationContext, $siteRepository, $destinationRepository);

$message = $templateManager->getTemplateComputed(
    $template,
    [
        'quote' => new Quote($faker->randomNumber(), $faker->randomNumber(), $faker->randomNumber(), $faker->date())
    ]
);

echo $message->subject . "\n" . $message->content;
