SELECT dli.lineItemId,
       dli.sku,
       dli.quantity quantityOrdered,
       dli.gtin,
       dli.itemType,
       dli.itemName,
       dli.singleGrossAmount,
       dli.vatRate,
       dli.attributes orderAttributes,
       do.currencyCode,
       a.articleNumber,
       a.brand,
       a.color,
       a.variant,
       a.weight,
       a.attributes itemAttributes,
       ai.url imageUrl,
       IFNULL(pli.quantity, 0) quantityPrepared,
       IFNULL(pali.quantityPacked, 0) AS quantityPacked,
       IFNULL(s.quantityShipped, 0) AS quantityShipped,
       IFNULL(po.quantity, 0) quantityPreparedCancellations,
       IF(f.id IS NOT NULL, IF(dli.quantity - IFNULL(s.quantityShipped,0) < 0, 0, dli.quantity - IFNULL(s.quantityShipped,0)), 0) quantityCancelled,
       IFNULL(prli.quantityReturned, 0) AS quantityPreparedReturns,
       IFNULL(r.quantityReturned,0) AS quantityReturned
FROM delivery_orders do
	     JOIN delivery_line_items dli ON dli.deliveryOrderId = do.id
	     LEFT JOIN fulfillments f ON f.deliveryOrderId = do.id
	     LEFT JOIN prepared_line_items pli ON pli.deliveryOrderId = dli.deliveryOrderId AND pli.lineItemId = dli.lineItemId
	     LEFT JOIN (
		SELECT  palii.lineItemId,
		        SUM(palii.quantity) quantityPacked
		FROM packed_line_items palii
			     JOIN packed_parcels ppi on palii.parcelId = ppi.id
		WHERE ppi.deliveryOrderId = :deliveryOrderId
		GROUP BY palii.lineItemId
	) pali ON pali.lineItemId = dli.lineItemId
	     LEFT JOIN prepared_oost po ON po.deliveryOrderId = dli.deliveryOrderId AND po.lineItemId = dli.lineItemId
	     LEFT JOIN (
		SELECT  slii.lineItemId,
		        SUM(slii.quantity) quantityShipped
		FROM fulfillments fi
			     LEFT JOIN shipped_parcels spi ON spi.fulfillmentId = fi.id
			     LEFT JOIN shipped_line_items slii ON slii.parcelId = spi.id
		WHERE fi.deliveryOrderId = :deliveryOrderId
		GROUP BY slii.lineItemId
	) s ON s.lineItemId = dli.lineItemId
	     LEFT JOIN (
		SELECT  prlii.lineItemId,
		        SUM(prlii.quantity) quantityReturned
		FROM prepared_return_line_items prlii
		WHERE prlii.deliveryOrderId = :deliveryOrderId
		GROUP BY prlii.lineItemId
	) prli ON prli.lineItemId = dli.lineItemId
	     LEFT JOIN (
		SELECT  rlii.lineItemId,
		        SUM(rlii.quantity) quantityReturned
		FROM returns ri
			     LEFT JOIN returned_line_items rlii ON rlii.returnId = ri.id
		WHERE ri.deliveryOrderId = :deliveryOrderId
		GROUP BY rlii.lineItemId
	) r ON r.lineItemId = dli.lineItemId
	     LEFT JOIN articles a ON a.logisticianId = do.logisticianId AND a.sku = dli.sku
	     LEFT JOIN article_images ai ON ai.logisticianId = do.logisticianId AND ai.sku = dli.sku AND ai.type = 'front'
WHERE do.id = :deliveryOrderId
GROUP BY dli.lineItemId
