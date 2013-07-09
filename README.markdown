[The Simple PHP Framework](http://github.com/tylerhall/simple-php-framework/) is a pragmatic approach to building websites with PHP 5. It's geared towards web design shops and freelance programmers looking for a common foundation to quickly bring web projects to life. Without getting too technical, SPF follows the [no-framework Framework](http://toys.lerdorf.com/archives/38-The-no-framework-PHP-MVC-framework.html) method coined by Rasmus Lerdorf -- with a little [Active Record](http://en.wikipedia.org/wiki/Active_record_pattern) thrown in for good measure.

### Project History ###

This framework is the foundation that all of my websites are built with. I've been using this code base (or some form of it) for seven years across hundreds of different projects - both personal and professional. It's served me well for the smallest of projects up to sites receiving millions of visitors per month. Less framework and more foundation, it provides a quick starting point and does a lot of the grunt work &mdash; user authentication, database calls, RSS feeds, etc. It's exactly enough to get your project bootstrapped and moving forward quickly.

This framework wasn't built overnight or even on purpose. It's really a development pattern and collection of classes that have evolved naturally over the last seven years. I've tried to walk a fine line and not add unnecessary features that most people won't use. I've done my best to keep it as minimal as possible yet still allow plenty of flexibility.

The Simple PHP Framework is designed to _help_ you build websites &mdash; not build them for you. There are plenty out there that already try to do that.

> All the web frameworks in the world won't turn a shitty programmer into a good one." &mdash;&nbsp;[uncov](http://www.uncov.com/2007/5/4/contactify-the-hello-world-of-web-2-0)

A branch of the framework has been forked internally at Yahoo!. Improvements from that branch will make their way back into the main trunk as appropriate.

### Download the Code ###

The Simple PHP Framework is hosted on [GitHub](http://github.com/tylerhall/simple-php-framework/)
and licensed under the [MIT Open Source License](http://www.opensource.org/licenses/mit-license.php).

### Documentation and Examples ###

As is the tradition with most open source software, the code is self-documenting &mdash; which is a nice way of saying I'm too lazy to write any formal documentation myself. That said, I'm always happy to answer questions about the code. You're also welcome to join our [discussion group](http://groups.google.com/group/simple-php-framework). There's not much activity, but if you ask a question you'll typically get an answer back quickly.

If you'd like to see a full website built using the framework, take a look at [Shine](https://github.com/tylerhall/Shine). It's a good, (mostly) clean example of how to use the framework.

### Misc ###

This framework has been around for a while, as such, the database layer uses the old PHP MySQL extenion. It still works just fine, but it's not the recommended PHP way any longer. Here's a [MySQL PDO](https://github.com/maxfierke/arcanecms/blob/master/includes/class.database.php) replacement written by Max Fierke.
