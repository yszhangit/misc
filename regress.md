# regression between multiple time serie data
not focus of presentation but what's the heck

- The forecast variable  y is sometimes also called the regressand, dependent or explained variable. 
- The predictor variables  x  are sometimes also called the regressors, independent or explanatory variables

# linear
```
autoplot(uschange[,c("Consumption","Income")]) 
summary(tslm(Consumption ~ Income, data=uschange))
```

# multi-liner
```
uschange %>%
  as.data.frame() %>%
  GGally::ggpairs()
```

# seasonal dummy
seasons are created by tslm automaticly
```
fit.beer <- tslm(beer2 ~ trend + season)
summary(fit.beer)
```
## Fourier series
An alternative to using seasonal dummy variables, especially for long seasonal periods, is to use Fourier terms
```
fourier.beer <- tslm(beer2 ~ trend + fourier(beer2, K=2))
summary(fourier.beer)
```
k is home many pairs of sin and cos to include, max of k is half of season

# select predictors
`CV({a fit model})' output:
- adjusted R^2
high is better, less accurate selecting for forecasting
- CV (cross-validation)
small is better, just remove one and see error, repeat removal for all
- AIC(Akaike's Information Criterion)
small is better, good for forecasting
- AICc (corrected AIC)
reduce bais when AIC select too many predictors
BIC/SBIC/SC (Schwarz’s Bayesian Information Criterion)
small is better

## best subset regression

## stepwise regression
if there are too many predictors
- backwards, incinldue all then reducing
- foreward, if backward cant handle too many predictor
- set position

## forecasting with lm

```
beer2 <- window(ausbeer, start=1992)
fit.beer <- tslm(beer2 ~ trend + season)
fcast <- forecast(fit.beer)
autoplot(fcast) +
  ggtitle("Forecasts of beer production using regression") +
  xlab("Year") + ylab("megalitres")
```
## forecasting with non-linear
### log linear
### splines
natural cubic smoothing spline

