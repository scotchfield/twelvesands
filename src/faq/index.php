<?
  require_once "../ts_core.php";
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>Twelve Sands FAQ</title></head>
<body>

<link rel="stylesheet" type="text/css" href="<? echo sg_app_root; ?>css/site.css" />
<div class="container">

<img src="<? echo sg_app_root; ?>images/ts_logo.gif" width="640" height="149">

<?

if ( ! isset( $_GET[ 'page' ] ) ) {
    $page = '1';
} else {
    $page = $_GET[ 'page' ];
}

?>

<p>
<a href="?page=1">What is Twelve Sands?</a> |
<a href="?page=2">New Player Guide</a> |
<a href="?page=3">Gameplay Questions</a> |
<a href="?page=4">Who's behind it?</a>
</p>
<hr />

<?

if ( '1' == $page ) {

?>

<h2>What is Twelve Sands?</h2>

<p>Twelve Sands is a free-to-play browser-based RPG, and although it's in
a playable beta state, it's still evolving day by day.</p>

<h2>What's different?</h2>

<p>Although Twelve Sands allows a player to choose a character class after
some time, they are by no means restricted to that play style.  Classes are
determined by learning skills that then allow other abilities to come in to
play.  Multi-classing is easy, and encourages players to try new abilities
and strategies without feeling locked in to a class based on a decision made
on the first day of play.</p>

<p>The world is an open one, and the only limitation is a character's level
(mainly for safety reasons!)  The story is rich, and constantly developing.
And it's browser-based, without the need for any additional software, so
you can play it anywhere.</p>

<h2>How do I play?</h2>

<p>You can sign up from the
<a href="<? echo sg_app_root; ?>register.php">registration page</a>!
If you have any problems, or notice anything that looks broken, please
let me know.</p>

<?

} elseif ( '2' == $page ) {

?>

<h2>What do I do first?</h2>

<p>If you're a new player, and you're unsure of what to do, here are a few
pointers to get you moving.</p>

<p><b>The Basics</b></p>

<p>When you first sign in to Twelve Sands, you'll find yourself in the
middle of Capital City.  On the top left side of the screen is a section
indicating your name, your level, how much gold you have, and your health.
On the right, you'll see a reminder of where in the world you're located.
In this case, you're in Capital City.  At the very bottom of the screen
are two bars indicating your XP (experience points, accumulated through
activities like combat), and your fatigue (a measure of how much you've
done so far today).  Your fatigue will reset to 0% each night, so the goal
is to do as much as you can each day without getting too tired.</p>

<p>Just underneath the title bar which reads Capital City is a description
of the area.  Since Twelve Sands is primarily a text-based game, it's
helpful to read the
description to get an idea of where you are, and what you might intend to
accomplish in each zone.  Below this is a list of places that are adjacent
to your current location.  Combat zones and other special areas are
identified as such, so if you read carefully, you won't end up in combat
unexpectedly.</p>

<p><b>Artifacts and Equipment</b></p>

<p>In order to engage in combat with any degree of success, you're going to
need to pick up a weapon, and some armour!  By default, you start out with
some basic equipment, but you might be interested in upgrading to something
a little better.</p>

<p>Head in to the Initiate's Corridors, an area designed to help characters
who are new to the world.  Next, go to The Weapon House, a small shop that can
offer you a way to better equip yourself for combat.  Move your mouse to
the list that says "Artifacts for sale", and hover over some of the
items in the store.  You'll see a popup box that lets you know about the
artifacts in the store, and what the benefits of each are.  For now,
look at the Ornamental Dagger.  It does the most damage out of the weapons
available at your level, since the Carving Knife is a level 3 artifact.
You're only level 1, so the Ornamental Dagger is your best bet.  Click on
the "buy" button, and you'll purchase one for yourself.</p>

<p>Now, click on your name, in the top-left
corner of the screen.  This is your character page.  On the right side,
you'll see a list of artifacts that you have equipped, like weapons and
armour.  If you want to equip the artifact that you just purchased,
you can click on the link immediately under "Equipped Weapon" that reads
"Equip something else."  You should now see a list of the artifacts that
you own.  Move your mouse to the Ornamental Dagger that you just purchased,
and click on the "equip" link beside it.  You have now equipped a stronger
weapon, and should be more effective in combat!</p>

<p>Remember, merely buying an artifact doesn't equip it automatically, so if
you decide to upgrade in the future, make sure to return here to equip
the new pieces!</p>

<p><b>Engaging in Combat</b></p>

<p>Now that you're all ready to go, it's time to return to Capital City
to find a fight!  If you ever need a quick way to return to Capital City
quickly, scroll to the very bottom of the page.  There will be a link
that actually says "Capital City", which you can click on, in order to
return to your home area.  Click this now, and you should move back home.
Look at the section of the page that says "Combat and Travel", and you
will see a zone called The Peaceful Meadows.  Click on this link.</p>

<p>Once you arrive in the Peaceful Meadows, you should see a number of
things.  A new description will let you know what the zone is like, and
who (if anyone) is around with quests and extra information.  In our case,
we just want to get in a fight quickly.  Look down to the Combat and Travel
section for this zone, and you'll see something that says Disorganized
Kobold Camp.  You can see that beside this link, it's identified as a
combat zone.  This means that if you click on the link with the zone's
name, you'll be getting ready to fight a foe in the zone.  Since you've
equipped your armour and weapon, go right ahead!</p>

<p>In combat, you'll see a set of prompts very similar to navigating through
the world.  After a description of the outcome is given, you have options
like attacking with your weapon, running away, or using items that you may
have accumulated through your travels.  In this case, click on the attack
link.  You might miss at first, or do a small amount of damage, but if
you've equipped some armour and a good sturdy weapon, you should have no
problem slaying your first monster.</p>

<p>If you've made it this far, congratulations!  You can return to Capital
City at almost any time (if you're in the middle of a fight, you'll have to
finish that first before you can get home!).  There is an infirmary if you're
ever really injured, but note that you'll restore some health after each
fight.  If you keep at it, you'll increase in levels, and get the chance to
learn some interesting and helpful new skills to assist you in your
travels.</p>

<p>Above all, stay focused, and have fun in the Twelve Sands!</p>

<?

} elseif ( '3' == $page ) {

?>

<h2>What is fatigue?</h2>

<p>Fatigue is a way to help curb daily adventures by limiting the amount
of actions a player can take in a day.  There is a maintenance period each
night at midnight EST where the fatigue is reset for each player.  While
adventuring, fishing, running away, dying, and other actions like that,
you'll accumulate fatigue.  When you hit the limit, you've got to wait
until the next day before taking more time to explore in Twelve Sands.</p>

<h2>Where should I adventure?</h2>

<p>Based on your level, here are some good options for gaining experience,
and getting the appropriate item drops.</p>

<p><b>Level 1:</b>
Disorganized Kobold Camp (from The Peaceful Meadows)</p>

<p><b>Level 2:</b>
Abandoned Farmhouse (from The Peaceful Meadows)<br>
The Boar Fields (from The Bustling Farmlands)</p>

<p><b>Level 3:</b>
Fortified Iron Hand Rebel Camp (from The Peaceful Meadows)<br>
The Misted Shores (from Green River)<br>
Savage Plains (from The Wild Meadows)</p>

<p><b>Level 4:</b>
Deteriorated Forest Trail (from Omor's Forest - Western Edge)<br>
The Undying Timepiece (Morning) (from The Undying Timepiece)</p>

<p><b>Level 5:</b>
Cavernous Bear Cave (from Omor's Forest - Western Edge)<br>
Abandoned Copper Mine (from Omor's Forest - Western Edge)<br>
The Green River Shorelines (from Green River)</p>

<p><b>Level 6:</b>
Abandoned Copper Depths (from Omor's Forest - Western Edge)<br>
The Undying Timepiece (Mid-Day) (from The Undying Timepiece)</p>

<p><b>Level 7:</b>
Enchanted Nook (from Green Pond)<br>
The Vile Swamplands (Entrance) (from The Vile Meadows)</p>

<p><b>Level 8:</b>
Base of the Green Waterfall (from Green Pond)<br>
Sandstorm Outpost (from Omor's Defense)</p>
The Iron Hand Outpost (from Omor's Defense)<br>
The Undying Timepiece (Evening) (from The Undying Timepiece)<br>
The Vile Swamplands (Depths) (from The Vile Meadows)</p>

<p><b>Level 9:</b>
The Rushing Rapids (from The Descending Coves)<br>
The Sand Mines (from Omor's Defense)<br>
The Vile Swamplands (Heart) (from The Vile Meadows)</p>

<p><b>Level 10:</b>
The Undying Timepiece (Night) (from The Undying Timepiece)<br>
The Sand Mines (Earth Tear) (from The Sands of Omor - Northern Edge)<br>
Deserted Guard's Barracks (from The Sands of Omor - Northern Edge)<br>
The Fiery Moats (from The Sands of Omor - Northern Edge)</p>

<?

} elseif ( '4' == $page ) {

?>

<h2>How can I support the project?</h2>

<p>Just play!  The game is free to play, and will always have a free
component.  In addition, the game will never have advertisements in it.
There will never be a required monthly fee in order to sign in.  This
is a game that I'm writing for enjoyment, and for the challenge of it.
If it becomes something greater down the road, you can be assured that
you will never be forced into a situation where you have to decide to
pay or to quit.  That's not fun.</p>

<p>If you do want to support the project financially, there is a donation
option that you can find after you've logged in to the game itself.
Running the game isn't free for me, and I am covering the monthly server
cost through the support of the players.  Every donation makes a difference,
and helping me to cover some of the monthly fees means you get an in-game
artifact reward.</p>

<p>If you're a webmaster and are looking for a good host, another really
great way to lend a hand would be to sign up through Dreamhost and to let
them know you came through Twelve Sands.  This is NOT required, by any means!
However, they've been hosting sites of mine for almost four years now, and
it's been great.  If you do sign up this way, please let me know, so that I
can say thank you!</p>

<p><a href="http://www.dreamhost.com/r.cgi?67241"><img src="80x15-d.png" style="border: 0;"></a></p>

<h2>Who are you?</h2>

<p>A Canadian guy who likes this sort of thing.  :)</p>

<p>Seriously though, <a href="http://www.scootah.com/">my name is Scott</a>,
and <a href="http://www.cs.queensu.ca/home/scott/">I'm a researcher</a>
at Queen's University in Kingston, Ontario.  Thanks for checking out
the game!</p>

<h2>Why do this?</h2>

<p>There are a lot of really fun games out there, and sometimes it's more
fun to take a few minutes out of your day to play a browser-based game than
it is to download a multi-gig operating-system-specific client, while paying
$15 a month for the joy of a graphical adventure.
<a href="http://www.kingdomofloathing.com">Kingdom of Loathing</a> is a great
example of this, and it's been a huge inspiration for me.  If you haven't
played it, please go!</p>

<p>The selfish reason that I do this is because I'm writing the type of game
that I want to play.  I <i>am</i> a player here, I always intend to be,
and I'm interested in playing something fun.  It's a great way to learn
gaming mechanics, and to hone my coding abilities in this kind of environment.
In addition, someone actually told me that they were a bit envious because
it looked like running Twelve Sands seemed like a lot of fun.  They're right
(hey killotron - thanks for that, it meant a lot!), and I'm having a blast
so far.  I do it because it's fun, not to turn a profit, or for any other
insidious reason.  :)</p>

<?

} else {

?>

<h2>I'm a hacker, trying to hack your game.  Can you help me?</h2>

<p>Sure thing!  When you're third level, purchase 42 Ornamental Longswords
from Thog's Weapons, and you might find an interesting surprise.</p>

<?

}

?>

<hr />
<p class="no_space"><a href="<? echo sg_app_root; ?>">Go back to Twelve Sands!</a></p>
</div>

</body></html>
