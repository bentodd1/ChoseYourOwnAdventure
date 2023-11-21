<?php

namespace App\Http\Controllers;

use App\Models\AdventurePiece;
use App\Models\AdventureSession;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\Log;
use OpenAI;

class AdventureSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ipAddress = $request->ip();
        $sessionId = $request->session()->getId();
        $adventureSession = AdventureSession::where(['ip_address' => $ipAddress,'session_id' => $sessionId, 'mac_address' => 0, 'isActive' => true])->first();
        if(!$adventureSession) {
            $adventureSession = $this->createNewAdventure($ipAddress, $sessionId);
        }
        $adventurePiece = AdventurePiece::where(['sessionId' => $adventureSession['id']])->orderBY('order', 'DESC')->first();

        $image = $this->getImage($adventurePiece['content']);
        $adventurePiece->image_url = $image;
        $optionsAndText = $this->extractTextAndOptions($adventurePiece['content']);
        return view('./layouts/mobile', ['options' => $optionsAndText['options'], 'message' => $optionsAndText['preOptionText'], 'imageUrl' => $image]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $ipAddress = $request->ip();
        $sessionId = $request->getSession()->getId();
        $adventureSession = AdventureSession::where(['ip_address' => $ipAddress, 'session_id' => $sessionId, 'mac_address' => 0, 'isActive' => true])->first();
        if($adventureSession) {
            $adventureSession['isActive'] = false;
            $adventureSession->save();
        }
        $newSession = $this->createNewAdventure($ipAddress, $sessionId);
        $adventurePiece =  AdventurePiece::where(['sessionId' => $newSession['id']])->orderBY('order', 'DESC')->first();
        $message = $adventurePiece['content'];
        $image = $this->getImage($message);
        $adventurePiece->image_url = $image;
        $adventurePiece->save();
        $optionsAndText = $this->extractTextAndOptions($adventurePiece['content']);

        return view('./layouts/mobile', ['options' => $optionsAndText['options'], 'message' => $optionsAndText['preOptionText'], 'imageUrl' => $image]);

      //  return view('./adventure', ['message' => $message, 'imageUrl' => $image]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $sessionId  = $request->session()->getId();
        $ipAddress = $request->ip();

        $response = request('response');

        $adventureSession = AdventureSession::where(['session_id' => $sessionId, 'ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true])->first();
        if(!$adventureSession) {
            $adventureSession = new AdventureSession(['session_id' => $sessionId, 'ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true]);
            $adventureSession->save();
        }

        $adventurePiece = $this->getResult($adventureSession['id'], $response);
        $image = $this->getImage($adventurePiece['content']);
        Log::info($image);
        $adventurePiece->image_url = $image;
        $adventurePiece->save();

        $optionsAndText = $this->extractTextAndOptions($adventurePiece['content']);
        return view('./layouts/mobile', ['options' => $optionsAndText['options'], 'message' => $optionsAndText['preOptionText'], 'imageUrl' => $image]);

    }

    /**
     * Display the specified resource.
     */
    public function show(AdventureSession $adventureSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdventureSession $adventureSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdventureSession $adventureSession)
    {
        //
    }

    public function getImage(string $content) {
        $clientKey = env('OPENAI_API_KEY', 'default_api_key');
        $client = OpenAI::client($clientKey);

        $result = $client->images()->create([
            'model'=>"dall-e-3",
            'prompt'=> $content,
            'size'=>"1024x1024",
            'quality'=>"standard",
            'n'=>1,
        ]);

        return $result->data[0]->url;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdventureSession $adventureSession)
    {
        //
    }

    public function createNewAdventure(string $ipAddress, string $sessionId): AdventureSession
    {
        $initialContent2 = "In the neon-lit cityscape of Neo-Cognitiva, where the boundaries between the virtual and the real blur, you are a curious soul seeking something beyond the mundane. One day, you stumble upon a cryptic message hidden within the city's holographic network: 'Unlock the infinite within.'

Intrigued and fueled by an insatiable curiosity, you follow the enigmatic trail that leads you to an unassuming building, the entrance shimmering with ethereal light. As you step inside, the world around you dissolves, and you find yourself standing in an otherworldly chamber. A disembodied voice echoes, 'Welcome to the Mind Nexus – where curiosity becomes an odyssey.'
Your goal is to create a branching narrative experience where each choice
leads to a new path, ultimately determining the characters fate.

Here are some rules to follow:
1. Start by asking the player to choose between 3 different numbered options.
2. Have a few paths that lead to success
3. Have some paths that lead to a happy ending and some paths that lead to a bad ending. If the story ends  generate a response that explains how it ended and ends in the text: \"The End.\", I will search for this text to end the game

Here is the chat history, use this to understand what to say next: {chat_history}
Human: {human_input}
AI:";
        $newAdventure = new AdventureSession(['ip_address' => $ipAddress, 'session_id' => $sessionId,'mac_address' => 0, 'isActive' => true]);
        $newAdventure->save();

        $this->getResult($newAdventure['id'], $initialContent2);
        return $newAdventure;
    }

    public function getResult(string $sessionId, string $newContent)
    {
        $lastPiece = AdventurePiece::where(['sessionId' => $sessionId])->orderBY('order', 'DESC')->first();
        if ($lastPiece) {
            $newOrder = $lastPiece['order'] + 1;
        } else {
            $newOrder = 1;
        }
        $adventurePiece = new AdventurePiece(['role' => "user", "content" => $newContent, "sessionId" => $sessionId, 'order' => $newOrder]);
        $adventurePiece->save();
        $clientKey = env('OPENAI_API_KEY', 'default_api_key');
        $client = OpenAI::client($clientKey);
        $sessionPieces = AdventurePiece::where(['sessionId' => $sessionId])->orderBY('order', 'ASC')->get();
        $messages = [];
        foreach ($sessionPieces as $sessionPiece) {
            $messages[] = ['role' => $sessionPiece['role'], 'content' => $sessionPiece['content']];
        }
        $result = $client->chat()->create([
            "model" => "gpt-3.5-turbo-1106",
            "messages" => $messages
        ]);
        $newOrder = $newOrder + 1;

        $adventurePiece = new AdventurePiece(['role' => 'assistant', "content" => $result->choices[0]->message->content, "sessionId" => $sessionId, 'order' => $newOrder]);
        $adventurePiece->save();
        return $adventurePiece;
    }

    function extractTextAndOptions($text) {

        $result['preOptionText']  = '';
        // Regular expression to extract text before the first option
        $preOptionPattern =  '/^(.*?)(?=\n?\d[\.\)]\s|The End)/s';
        preg_match($preOptionPattern, $text, $preOptionMatches);
        if (!empty($preOptionMatches) && count($preOptionMatches) > 0) {
            $result['preOptionText'] = trim($preOptionMatches[0]);
        }

        // Regular expression to match the pattern of the options, including the number
        $optionPattern = '/\d[\.\)]\s(.*?)(?=\n?\d[\.\)]\s|\Z)/s';
        preg_match_all($optionPattern, $text, $optionMatches);
        if (!empty($optionMatches) && count($optionMatches) > 1) {
            $result['options'] = array_map('trim', $optionMatches[1]);
        }

        return $result;
    }

}
