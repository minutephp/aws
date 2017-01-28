<?php
/**
 * User: Sanchit <dev@minutephp.com>
 * Date: 8/25/2016
 * Time: 5:24 AM
 */
namespace Minute\Event {

    class DockerEvent extends Event {
        const DOCKER_INCLUDE_FILES = "docker.include.files";
        /**
         * @var array
         */
        private $files;
        /**
         * @var array
         */
        private $settings;
        /**
         * @var string
         */
        private $type;
        /**
         * @var array
         */
        private $tags;

        /**
         * DockerEvent constructor.
         *
         * @param array $settings
         * @param string $type
         */
        public function __construct(array $settings = [], string $type, array $tags = []) {
            $this->files    = [];
            $this->settings = $settings;
            $this->type     = $type;
            $this->tags     = $tags;
        }

        /**
         * @return array
         */
        public function getTags(): array {
            return $this->tags ?? [];
        }

        /**
         * @param array $tags
         *
         * @return DockerEvent
         */
        public function setTags(array $tags): DockerEvent {
            $this->tags = $tags;

            return $this;
        }

        /**
         * @param array $tags
         *
         * @return DockerEvent
         */
        public function addTags(array $tags): DockerEvent {
            $this->tags = array_merge($this->tags ?? [], $tags);

            return $this;
        }

        /**
         * @return string
         */
        public function getType(): string {
            return $this->type;
        }

        /**
         * @param string $type
         *
         * @return DockerEvent
         */
        public function setType(string $type): DockerEvent {
            $this->type = $type;

            return $this;
        }

        /**
         * @return array
         */
        public function getSettings(): array {
            return $this->settings;
        }

        /**
         * @param array $settings
         *
         * @return DockerEvent
         */
        public function setSettings(array $settings): DockerEvent {
            $this->settings = $settings;

            return $this;
        }

        /**
         * @return array
         */
        public function getFiles(): array {
            return $this->files;
        }

        /**
         * @param array $files
         *
         * @return DockerEvent
         */
        public function setFiles(array $files): DockerEvent {
            $this->files = $files;

            return $this;
        }

        /**
         * @param string $file
         *
         * @return mixed|string
         */
        public function getContent(string $file) {
            return ($this->files[$file] ?? '');
        }

        /**
         * @param string $file
         * @param string $content
         *
         * @return $this
         */
        public function setContent(string $file, string $content) {
            $this->files[$file] = $content;

            return $this;
        }

        /**
         * @param string $file
         * @param string $content
         *
         * @return DockerEvent
         */
        public function addContent(string $file, string $content) {
            $this->files[$file] = trim($this->getContent($file) . "\n" . $content) . "\n";

            return $this;
        }
    }
}