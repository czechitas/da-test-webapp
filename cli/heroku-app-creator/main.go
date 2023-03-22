package main

import (
	"crypto/rand"
	"encoding/base64"
	"flag"
	"fmt"
	"github.com/fatih/color"
	"log"
	"os"
	"os/exec"
	"strings"
)

/*
heroku login

./heroku-app-creator -awsAccess xxx -awsSecret xxx -awsBucketUrl xxx -numapps 1 -dryrun

GOOS=darwin GOARCH=amd64 go build -o heroku-app-creator main.go
GOOS=windows GOARCH=amd64 go build -o heroku-app-creator.exe main.go

*/

func runCommand(command string, dry bool) {
	if dry {
		//fmt.Printf("%s[DRY-RUN] %s%s\n", Yellow, command, ResetColor)
		color.Yellow("[DRY-RUN] %s", command)
	} else {
		//fmt.Printf("%s[EXECUTING] %s%s\n", Green, command, ResetColor)
		color.Green("[EXECUTING] %s", command)
		cmd := exec.Command("bash", "-c", command)
		cmd.Stdout = os.Stdout
		cmd.Stderr = os.Stderr
		if err := cmd.Run(); err != nil {
			//log.Fatalf("%sError executing command: %s\n%s%s", Red, command, err, ResetColor)
			log.Fatalf(color.RedString("%sError executing command: \n%s\n%s", command, err))
		}
	}
}

var teams = [7]string{
	"bug-busters",
	"bugitas",
	"testing-queens",
	"it-tykve",
	"lady-bugs",
	"dex",
	"buginy",
}

func main() {
	// Parse command-line arguments
	var dryRun bool
	var numApps int

	var awsAccessKeyId string
	var awsSecretAccessKey string
	var awsBucketUrl string

	flag.BoolVar(&dryRun, "dryrun", false, "Enable dry run mode (do not execute commands)")
	flag.IntVar(&numApps, "numapps", 1, "Number of branches and apps to create")

	flag.StringVar(&awsAccessKeyId, "awsAccess", "", "AWS_ACCESS_KEY_ID")
	flag.StringVar(&awsSecretAccessKey, "awsSecret", "", "AWS_SECRET_ACCESS_KEY")
	flag.StringVar(&awsBucketUrl, "awsBucketUrl", "", "URL to AWS bucket")

	flag.Parse()

	// Create branches and apps
	for i := 0; i <= len(teams); i++ {
		if i == numApps+1 {
			break
		}

		//branchName := fmt.Sprintf("team-%d", i)
		//appName := fmt.Sprintf("da-test-webapp-team-%d", i)

		branchName := teams[i]
		appName := teams[i]

		//fmt.Printf("%sCreating app %d: %s%s\n", Cyan, i, appName, ResetColor)
		color.Cyan("Creating app %s", appName)

		// Create a new Git branch
		runCommand(fmt.Sprintf("git branch %s", branchName), dryRun)

		// Create a new Heroku app
		runCommand(fmt.Sprintf("heroku create %s --region eu", appName), dryRun)

		// Add ClearDB MySQL addon to the Heroku app
		runCommand(fmt.Sprintf("heroku addons:create cleardb:ignite -a %s", appName), dryRun)

		// Specify buildpacks
		runCommand(fmt.Sprintf("heroku buildpacks:add --index 1 heroku/nodejs -a %s", appName), dryRun)
		runCommand(fmt.Sprintf("heroku buildpacks:add --index 2 heroku/php -a %s", appName), dryRun)

		var dbURL string
		if !dryRun {
			dbURLBytes, err := exec.Command("bash", "-c", fmt.Sprintf("heroku config:get CLEARDB_DATABASE_URL -a %s", appName)).Output()
			if err != nil {
				log.Fatal(err)
			}
			dbURL = strings.TrimSpace(string(dbURLBytes))
		} else {
			dbURL = "mysql://bfb017fe42ce85:f14a15cc@eu-cdbr-west-03.cleardb.net/heroku_d057fc734b47ce4?reconnect=true"
		}

		// Parse DB information from the URL
		dbUsername := strings.Split(dbURL, "/")[2]
		dbUsername = strings.Split(dbUsername, ":")[0]

		dbPassword := strings.Split(dbURL, ":")[2]
		dbPassword = strings.Split(dbPassword, "@")[0]

		dbHost := strings.Split(dbURL, "@")[1]
		dbHost = strings.Split(dbHost, "/")[0]

		dbName := strings.Split(dbURL, "/")[3]
		dbName = strings.Split(dbName, "?")[0]

		// AWS
		awsDefaultRegion := "eu-central-1"
		awsBucket := "da-test-webapp"

		// Generate a random APP_KEY
		appKeyBytes := make([]byte, 32)
		if _, err := rand.Read(appKeyBytes); err != nil {
			log.Fatal(err)
		}
		appKey := base64.StdEncoding.EncodeToString(appKeyBytes)

		// Set default environment variables for the Heroku app
		runCommand(fmt.Sprintf(`heroku config:set -a %s \
            DB_HOST=%s \
            DB_DATABASE=%s \
            DB_USERNAME=%s \
            DB_PASSWORD=%s \
            DB_PORT=3306 \
            APP_KEY=%s`,
			appName, dbHost, dbName, dbUsername, dbPassword, appKey,
		), dryRun)

		// Set AWS environment variables for the Heroku app
		runCommand(fmt.Sprintf(`heroku config:set -a %s \
            FILESYSTEM_DRIVER=s3 \
            AWS_ACCESS_KEY_ID=%s \
            AWS_SECRET_ACCESS_KEY=%s \
            AWS_DEFAULT_REGION=%s \
            AWS_BUCKET=%s \
            AWS_URL=%s`, // pro linky v aplikaci
			appName, awsAccessKeyId, awsSecretAccessKey, awsDefaultRegion, awsBucket, awsBucketUrl,
		), dryRun)

		// Commit the changes and push the branch to the remote repository
		//runCommand("git add .", dryRun)
		//runCommand(fmt.Sprintf("git commit -m 'Create new Heroku app %s with MySQL database and environment variables'", appName), dryRun)
		//runCommand(fmt.Sprintf("git push -u origin %s", branchName), dryRun)

		// Return to the main branch
		//runCommand(fmt.Sprintf("git checkout %s", baseBranch), dryRun)
	}
}
