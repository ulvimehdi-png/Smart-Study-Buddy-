.PHONY: dev-up dev-down prod-up prod-down

dev-up:
	make -C docker dev-up

dev-down:
	make -C docker dev-down

prod-up:
	make -C docker prod-up

prod-down:
	make -C docker prod-down