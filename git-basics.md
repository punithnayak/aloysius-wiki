# Your first Git repository 🌱

<walkthrough-tutorial-duration duration="10"></walkthrough-tutorial-duration>

**Git** remembers every version of your files: what changed, who changed it, when, and why. It is **View history** for code.

In 10 minutes you will make a repository, save two versions, and try ideas on **two branches** (two sandboxes) without breaking the main copy.

Type each command in the **terminal** at the bottom, or click the copy button next to it and paste.

Click **Start** to begin.

## Step 1: Tell Git who you are

Git writes your name on every save. Do this once (use your own name and email):

```sh
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
```

## Step 2: Make a repository

A repository is a folder that remembers its history:

```sh
mkdir -p ~/hello-git && cd ~/hello-git
git init -b main
```

You see `Initialized empty Git repository`. Your history is empty for now.

## Step 3: Save point one

Make a file and **commit** it (a commit is a save point with a message, like an edit summary on Wikipedia):

```sh
echo "Namaskara" > hello.txt
git add hello.txt
git commit -m "Add hello file"
```

`git add` puts the file in the box. `git commit` seals the box with a label.

## Step 4: Change it and see the difference

```sh
echo "Mangaluru" >> hello.txt
git diff
```

The green line starting with `+` is your change. This is exactly what a reviewer sees on Gerrit.

Now save it:

```sh
git commit -am "Add my city"
git log --oneline
```

Two commits: your own history!

## Step 5: Branch one, `kannada`

A **branch** is a sandbox: a copy where you can try an idea safely.

```sh
git switch -c kannada
echo "ನಮಸ್ಕಾರ" >> hello.txt
git commit -am "Add Kannada greeting"
cat hello.txt
```

## Step 6: Branch two, `places`

Go back to `main` and start a second sandbox from there:

```sh
git switch main
cat hello.txt
```

No Kannada line! `main` is untouched. Now the second branch:

```sh
git switch -c places
echo "Udupi, Manipal, Agumbe" > places.txt
git add places.txt
git commit -m "Add places I like"
```

## Step 7: See both branches

```sh
git log --oneline --graph --all
```

You see `main`, plus two branches growing from it: `kannada` and `places`. Each has its own commit.

```sh
git branch
```

The `*` shows the branch you are on.

## Step 8: Bring the good ideas back

Happy with both? **Merge** them into `main`:

```sh
git switch main
git merge kannada
git merge places -m "Merge places"
cat hello.txt places.txt
git log --oneline --graph
```

`main` now has the Kannada line and the places file. That is how every change reaches Wikipedia: on a branch first, then reviewed, then merged.

## Done!

<walkthrough-conclusion-trophy></walkthrough-conclusion-trophy>

| You did | Git word |
|---|---|
| Made a folder that remembers | `git init` (repository) |
| Saved versions with messages | `git commit` |
| Saw what changed | `git diff` |
| Read the history | `git log` |
| Tried ideas safely | `git switch -c` (branch) |
| Brought them back | `git merge` |

**Next at Wikimedia:** instead of merging yourself, you send your branch's commit for **code review** with `git review`, and a reviewer merges it.
