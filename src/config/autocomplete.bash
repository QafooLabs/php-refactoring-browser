#!bash
#
# bash completion support for php-refactoring-browser
#
# Copyright (C) 2013 Tobias Schlitt <toby@php.net>
#
# Code copied and adjusted from https://github.com/KnpLabs/symfony2-autocomplete:
# Copyright (C) 2011 Matthieu Bontemps <matthieu@knplabs.com>
# Distributed under the GNU General Public License, version 2.0.

_console()
{
    local cur prev cmd
    COMPREPLY=()
    cur="${COMP_WORDS[COMP_CWORD]}"
    prev="${COMP_WORDS[COMP_CWORD-1]}"
    cmd="${COMP_WORDS[0]}"
    PHP='$ret = shell_exec($argv[1] . " --no-debug --env=prod");

$comps = "";
$ret = preg_replace("/^.*Available commands:\n/s", "", $ret);
if (preg_match_all("@^  ([^ ]+) @m", $ret, $m)) {
    $comps = $m[1];
}

echo implode("\n", $comps);
'
    possible=$($(which php) -r "$PHP" $COMP_WORDS);
    COMPREPLY=( $(compgen -W "${possible}" -- ${cur}) )
    return 0
}

complete -F _console refactor
COMP_WORDBREAKS=${COMP_WORDBREAKS//:}
