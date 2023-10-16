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

        $adventureSession = AdventureSession::where(['ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true])->first();
        if(!$adventureSession) {
            $adventureSession = $this->createNewAdventure($ipAddress);
        }
        $adventurePiece = AdventurePiece::where(['sessionId' => $adventureSession['id']])->orderBY('order', 'DESC')->first();

        //$template="You are a helpful assistant that translates {input_language} to {output_language}.";

        return view('./adventure', ['message' => $adventurePiece['content']]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $ipAddress = $request->ip();
        $adventureSession = AdventureSession::where(['ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true])->first();
        if($adventureSession) {
            $adventureSession['isActive'] = false;
            $adventureSession->save();
        }
        $newSession = $this->createNewAdventure($ipAddress);
        return view('./adventure', ['message' => AdventurePiece::where(['sessionId' => $newSession['id']])->orderBY('order', 'DESC')->first()['content']]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $ipAddress = $request->ip();

        $response = request('response');

        $adventureSession = AdventureSession::where(['ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true])->first();
        if(!$adventureSession) {
            $adventureSession = new AdventureSession(['ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true]);
            $adventureSession->save();
        }

        $adventurePiece = $this->getResult($adventureSession['id'], $response);

        return view('./adventure', ['message' => $adventurePiece['content']]);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdventureSession $adventureSession)
    {
        //
    }

    public function createNewAdventure(string $ipAddress): AdventureSession
    {
        $initialContent = "I need help writing a choose your own adventure story. Each story snippet can only be 5 sentences.  Let's create a romantic novel.  The main character is a male.  After the Snippet provide 2 to 4 options";
        $initialContent2 = "You are now the guide of a mystical journey in the Whispering Woods.
A traveler named Elara seeks the lost Gem of Serenity.
You must navigate her through challenges, choices, and consequences,
dynamically adapting the tale based on the traveler's decisions.
Your goal is to create a branching narrative experience where each choice
leads to a new path, ultimately determining Elara's fate.

Here are some rules to follow:
1. Start by asking the player to choose some kind of weapons that will be used later in the game
2. Have a few paths that lead to success
3. Have some paths that lead to death. If the user dies generate a response that explains the death and ends in the text: \"The End.\", I will search for this text to end the game

Here is the chat history, use this to understand what to say next: {chat_history}
Human: {human_input}
AI:";
        $newAdventure = new AdventureSession(['ip_address' => $ipAddress, 'mac_address' => 0, 'isActive' => true]);
        $newAdventure->save();

        $this->getResult($newAdventure['id'], $initialContent2);
        return $newAdventure;
    }

    public function getResult(string $sessionId, string $newContent) {
        $lastPiece = AdventurePiece::where(['sessionId' => $sessionId])->orderBY('order', 'DESC')->first();
        if($lastPiece) {
         $newOrder = $lastPiece['order'] + 1;
        }
        else {
            $newOrder = 1;
        }
        $adventurePiece = new AdventurePiece(['role' => "user", "content" => $newContent, "sessionId" => $sessionId, 'order' => $newOrder]);
        $adventurePiece->save();
        $client = OpenAI::client("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");
        $sessionPieces = AdventurePiece::where(['sessionId' => $sessionId])->orderBY('order', 'ASC')->get();
        $messages = [];
        foreach ($sessionPieces as $sessionPiece) {
            $messages[] = ['role' => $sessionPiece['role'], 'content' => $sessionPiece['content']];
        }
        $result = $client->chat()->create( [
            "model" => "gpt-3.5-turbo",
            "messages" => $messages
        ]);
        $newOrder = $newOrder +1;

        $adventurePiece = new AdventurePiece(['role' => 'assistant', "content" => $result->choices[0]->message->content, "sessionId" => $sessionId, 'order' => $newOrder]);
        $adventurePiece->save();
        return $adventurePiece;
    }
}
