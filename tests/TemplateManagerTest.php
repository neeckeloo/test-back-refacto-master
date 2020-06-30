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
require_once __DIR__ . '/../src/Template/TagsProcessor.php';
require_once __DIR__ . '/../src/Template/QuoteTagsProcessor.php';
require_once __DIR__ . '/../src/Template/UserTagsProcessor.php';

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
    public function compute_template_by_replacing_tags()
    {
        $applicationContext = $this->createApplicationContext();

        $destinationId = $this->faker->randomNumber();
        $expectedDestination = $this->createDestination($destinationId);
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

        $templateManager = $this->createTemplateManager($applicationContext);

        $message = $templateManager->getTemplateComputed($template, [
            'quote' => $this->createQuote($destinationId),
            'destination' => $expectedDestination,
        ]);

        $expectedSubject = 'Votre livraison à ' . $expectedDestination->countryName;
        $expectedContent = "
Bonjour " . $expectedUser->firstname . ",

Merci de nous avoir contacté pour votre livraison à " . $expectedDestination->countryName . ".

Bien cordialement,

L'équipe Convelio.com
";

        $this->assertEquals($expectedSubject, $message->subject);
        $this->assertEquals($expectedContent, $message->content);
    }

    /**
     * @test
     */
    public function throw_exception_when_invalid_tags_processor_provided()
    {
        $template = new Template(1, 'subject', 'content');

        $templateManager = new TemplateManager(['invalid_tags_processor']);

        $this->expectException(\RuntimeException::class);

        $templateManager->getTemplateComputed($template, []);
    }

    private function createTemplateManager(ApplicationContext $applicationContext)
    {
        $quoteTagsProcessor = new QuoteTagsProcessor($applicationContext);
        $userTagsProcessor = new UserTagsProcessor($applicationContext);

        return new TemplateManager([$quoteTagsProcessor, $userTagsProcessor]);
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

    /**
     * @param int $destinationId
     * @return Destination
     */
    private function createDestination($destinationId)
    {
        return new Destination($destinationId, $this->faker->country, 'en', $this->faker->slug());
    }
}
