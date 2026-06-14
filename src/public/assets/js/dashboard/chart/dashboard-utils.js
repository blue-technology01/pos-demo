const formatUSD = val =>
    new Intl.NumberFormat(
        'en-US',
        {
            style:                 'currency',
            currency:              'USD',
            maximumFractionDigits: 0,
        }
    ).format(val);

const AXIS_LABEL_STYLE = {
    colors:   '#94a3b8',
    fontSize: '12px',
};
