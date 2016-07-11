#### Why are you using both Phing and Symfony commands?

While Phing and the Symfony Console can both accomplish some of the same tasks, they are different tools with different intended purposes. When developing functionality for BLT we are careful to choose the right tool for the right job.

Phing is intended to be build tool. It is particularly good at stringing together multiple commands and tasks into a single target which can then be executed procedurally. We use Phing when are requirements are well suited to this strength.

The commands that Phing executes can, of course, be provided by anything. Some are native linux commands, some are provided by tools like Composer and NPM, while others may be provided by the Symfony Console component.

As a rule, we _use Symfony console to provide fixed-scope commands_. These commands should be flexible and have absolutely no intrinsic awareness of the greater build process. We _use Phing to call commands within the context of a build process_, executing them with specific argument values at the correct time.

