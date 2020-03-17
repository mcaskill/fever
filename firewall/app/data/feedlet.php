<h2>Feed a Fever</h2>

<p>Conventional wisdom be damned, Fever needs fuel to burn.
<?php if (isset($show_opml_msg)) : ?>
Import your feeds from an existing OPML below and be sure
to d<?php else : e('D'); endif; ?>rag this <?php e($this->feedlet_link()); ?> onto your
browser bookmark bar. The feedlet allows you to subscribe
to any feed embedded in a page.</p>

<p>You can also subscribe via url in various applications:</p>

<pre><code><?php e($this->subscribe_link()); ?></code></pre>

<h2>Crank up the heat</h2>

<p>In order to get the most out of Fever you need to take
advantage of Sparks. Your regular subscriptions are like
kindling. Sparks are feeds that you subscribe to solely to
increase the temperature of the information you are most
interested in.</p>