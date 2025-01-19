import logging


class Logger:
    """Class Config For Logger"""

    def __init__(
        self,
        logger_name: str,
        log_file: str = "starters.log",
        log_level: int = logging.DEBUG,
    ):
        self.logger = logging.getLogger(logger_name)
        self.logger.setLevel(log_level)
        self.formatter = logging.Formatter(
            "\n%(levelname)s:     (%(name)s) ==  %(message)s  [%(lineno)d]"
        )

        # file handler
        # self.file_handler = logging.FileHandler(log_file)
        # self.file_handler.setFormatter(self.formatter)
        # self.logger.addHandler(self.file_handler)

        # console handler
        self.console_handler = logging.StreamHandler()
        self.console_handler.setFormatter(self.formatter)
        self.logger.addHandler(self.console_handler)

    def get_logger(self):
        return self.logger


logger = Logger("app").get_logger()
