<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Регистрация клиента Limmite</title>

	@vite(['resources/js/app.js', 'resources/css/register_form.css'])
</head>
<body>
	<div class="container" x-data="{
		processing: false,

		form: $form('post', location.href, {
			name: '',
			surname: '',
			birthday: '',
			phone: '+7',
			email: '',
		}),
		submit() {
			this.processing = true

			this.form.submit()
				.then(response => {
					alert('Новый пользователь зарегистрирован');
					this.$refs.form.reset()
				})
				.catch(error => {
					if (error.response.status > 500) {
						alert('Возникла ошибка: '+error.message);
						this.$refs.form.reset()
					}
				})
				.finally(() => {
					this.processing = false
				})
		},
	}">
		<form class="register__form" @submit.prevent="submit" x-ref="form">
			<div class="form__header">
				<div class="form__logo">
					<a href="https://limmite.ru">
						<img src="{{ Vite::asset('resources/images/logo-main.svg') }}" alt="">
					</a>
				</div>
				<div>Магазин брендовой одежды</div>
			</div>

			<div class="form__inputs">
				<div class="input" :class="form.invalid('name') && 'input_error'">
					<label class="input__label">
						<div class="input__title">Имя</div>
						<input type="text" class="input__value" 
							name="name"
							x-model="form.name"
							@change="form.validate('name')"
						>
					</label>
					<div class="input__error" x-text="form.errors.name"></div>
				</div>

				<div class="input" :class="form.invalid('surname') && 'input_error'">
					<label class="input__label">
						<div class="input__title">Фамилия</div>
						<input type="text" class="input__value" 
							name="surname"
							x-model="form.surname"
							@change="form.validate('surname')"
						>
					</label>
					<div class="input__error" x-text="form.errors.surname"></div>
				</div>

				<div class="input" :class="form.invalid('phone') && 'input_error'">
					<label class="input__label">
						<div class="input__title">Телефон</div>
						<input type="text" class="input__value" 
							name="phone"
							x-mask="+7 (999) 999-99-99"
							x-model="form.phone"
							@change="form.validate('phone')"
						>
					</label>
					<div class="input__error" x-text="form.errors.phone"></div>
				</div>

				<div class="input" :class="form.invalid('email') && 'input_error'">
					<label class="input__label">
						<div class="input__title">Email</div>
						<input type="text" class="input__value" 
							name="email"
							x-model="form.email"
							@change="form.validate('email')"
						>
					</label>
					<div class="input__error" x-text="form.errors.email"></div>
				</div>

				<div class="input" :class="form.invalid('birthday') && 'input_error'">
					<label class="input__label">
						<div class="input__title">День рождения</div>
						<input type="date" class="input__value" 
							name="birthday"
							x-model="form.birthday"
							@change="form.validate('birthday')"
						>
					</label>
					<div class="input__error" x-text="form.errors.birthday"></div>
				</div>
			</div>

			<button type="submit" class="form__submit"
				:disabled="form.processing || processing"
			>
				<span x-show="!processing">Зарегистрировать пользователя</span>
				<svg x-show="processing" x-cloak xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1rem" height="1rem" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
					<circle cx="50" cy="50" fill="none" stroke="#ffffff" stroke-width="10" r="40" stroke-dasharray="160.22122533307947 55.40707511102649">
					<animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1.7857142857142856s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
					</circle>
				</svg>
			</button>
		</form>
	</div>
</body>
</html>
