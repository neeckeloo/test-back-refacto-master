<?php

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

class TemplateManagerTest extends PHPUnit_Framework_TestCase
{
    private $faker;

    /**
     * Init the mocks
     */
    public function setUp()
    {
        $this->faker = \Faker\Factory::create();
    }

    /**
     * Closes the mocks
     */
    public function tearDown()
    {
    }

    /**
     * @test
     */
    public function test()
    {
        $applicationContext = $this->createApplicationContext();
        $destinationRepository = new DestinationRepository();

        $destinationId = $this->faker->randomNumber();
        $expectedDestination = $destinationRepository->getById($destinationId);
        $expectedUser = $applicationContext->getCurrentUser();

        $template = new Template(
            1,
            'Votre livraison à [quote:destination_name]',
            "
Bonjour [user:first_name],

Merci de nous avoir contacté pour votre livraison à [quote:destination_name].

Bien cordialement,

L'équipe Convelio.com
");

        $templateManager = new TemplateManager($applicationContext, $destinationRepository);

        $message = $templateManager->getTemplateComputed(
            $template,
            [
                'quote' => $this->createQuote($destinationId),
            ]
        );

        $this->assertEquals('Votre livraison à ' . $expectedDestination->countryName, $message->subject);
        $this->assertEquals("
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contacté pour votre livraison à " . $expectedDestination->countryName . ".

Bien cordialement,

L'équipe Convelio.com
", $message->content);
    }

    private function createApplicationContext()
    {
        $currentSite = new Site($this->faker->randomNumber(), $this->faker->url);
        $currentUser = new User($this->faker->randomNumber(), $this->faker->firstName, $this->faker->lastName, $this->faker->email);

        return new ApplicationContext($currentSite, $currentUser);
    }

    /**
     * @param int $destinationId
     * @return Quote
     */
    private function createQuote($destinationId)
    {
        return new Quote($this->faker->randomNumber(), $this->faker->randomNumber(), $destinationId, $this->faker->date());
    }
}
