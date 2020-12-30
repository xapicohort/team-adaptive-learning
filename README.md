# team-adaptive-learning
This plugin allows WordPress administrators to create adaptive learning experience with a few clicks. The plugin comes with three post types, two taxonomies and an adaptive algorithm that sequences content in real time. Learner data and real-time sequencing is managed entirely through the xAPI's state and statement resources, and saved to a Learning Record Store (LRS).

## Features
- Plugin settings screen to enter LRS endpoint, username and password. Object ID prefix used to normalize all statement object IDs.
- Content nodes used for instructional content. Can select Expert Model Item Taxonomy, content type, difficulty and other metadata.
- Assessment nodes used for formative assessments to determine mastery. Currently multiple choice questions are supported. Can select Expert Model Item taxonomy, difficulty and other metadata. 
- Auto generates confidence-based multiple choice questions that report detailed assessment statements to the LRS, including how confident learners are in their answers.
- Adaptive algorithm that sequences content in real-time, based on the learner's mastery of expert model items at specific difficulties. 
- Learner state is persisted using xAPI's state resource.

## To do
- Change current expert model items when difficulty level 3 is mastered.
- Configure adaptive algorithm via UI.
- Incorporate content type, learner preference and confidence into adaptive algorithm.
- Add more question types.

## License
GPL v2 or later

## Contributing
Pull requests, bug reports, and feature requests are welcome.
