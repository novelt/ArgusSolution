

## How to debug Angular from Visual Studio Code ?

### Step by step

 1. Install the [Chrome Debugger Extension](https://marketplace.visualstudio.com/items?itemName=msjsdiag.debugger-for-chrome)
 2. Create the [launch.json](https://code.visualstudio.com/Docs/editor/debugging#_launch-configurations)
 3. Use **launch.json** (see below)
 4. Create the [task.json](https://code.visualstudio.com/docs/editor/tasks#_custom-tasks)
 5. Use **task.json** (see below)
 6. Press CTRL + SHIFT + B
	 7. (or) **Tasks** > **Run build task** from VS Code
	 8. (or) npm start from the terminal
 7. Press F5
	 8. (or) **Debug** > **Start debugging** from VS Code

### launch.json

    {
      "version": "0.2.0",
      "configurations": [
        {
          "name": "Launch Chrome",
          "type": "chrome",
          "request": "launch",
          "url": "http://localhost:4200/#",
          "webRoot": "${workspaceRoot}"
        },
        {
          "name": "Attach Chrome",
          "type": "chrome",
          "request": "attach",
          "url": "http://localhost:4200/#",
          "webRoot": "${workspaceRoot}"
        },
        {
          "name": "Launch Chrome (Test)",
          "type": "chrome",
          "request": "launch",
          "url": "http://localhost:9876/debug.html",
          "webRoot": "${workspaceRoot}"
        },
        {
          "name": "Launch Chrome (E2E)",
          "type": "node",
          "request": "launch",
          "program": "${workspaceRoot}/node_modules/protractor/bin/protractor",
          "protocol": "inspector",
          "args": ["${workspaceRoot}/protractor.conf.js"]
        }
      ]
    }

### task.json

    {
        "version": "2.0.0",
        "tasks": [
          {
            "identifier": "ng serve",
            "type": "npm",
            "script": "start",
            "problemMatcher": [],
            "group": {
              "kind": "build",
              "isDefault": true
            }
          },
          {
            "identifier": "ng test",
            "type": "npm",
            "script": "test",
            "problemMatcher": [],
            "group": {
              "kind": "test",
              "isDefault": true
            }
          }
        ]
      }
