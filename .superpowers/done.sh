#!/bin/sh
# usage: done.sh <phase-slug> <task> <base> <commit-msg> <files...>
P=docs/superpowers/plans/2026-10-05-manager-$1.md; T=$2; B=$3; M=$4; shift 4
git add "$@" && git commit -q -m "$M

Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>" && /c/Users/caoth/.claude/plugins/cache/claude-plugins-official/superpowers/6.4.1/skills/executing-plans/scripts/task-done $P $T $B -- /e/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan test tests/Feature/Manager tests/Feature/Menu tests/Feature/Auth tests/Unit 2>&1 | tail -1 | cut -c1-90; git rev-parse --short HEAD
